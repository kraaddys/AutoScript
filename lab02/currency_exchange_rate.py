#!/usr/bin/env python3
"""
currency_exchange_rate.py
Interacts with the local Currency Exchange service API and saves results to JSON.
"""

from __future__ import annotations

import argparse
import json
import logging
import os
import re
import sys
from datetime import datetime, date
from pathlib import Path
from typing import Any, Dict, Optional

import requests


# --- Paths ---
# Project root is assumed to be the parent of this script's folder (lab02/)
SCRIPT_PATH = Path(__file__).resolve()
PROJECT_ROOT = SCRIPT_PATH.parents[1]  # .../<project_root>/lab02/currency_exchange_rate.py
DATA_DIR = PROJECT_ROOT / "data"
ERROR_LOG = PROJECT_ROOT / "error.log"


def setup_logging() -> None:
    """Configure logging to file and console for errors."""
    DATA_DIR.mkdir(parents=True, exist_ok=True)  # ensure data exists (not strictly needed for logging)
    handlers = [logging.FileHandler(ERROR_LOG, encoding="utf-8")]
    logging.basicConfig(
        level=logging.INFO,
        format="%(asctime)s [%(levelname)s] %(message)s",
        handlers=handlers,
        force=True,  # ensure our config is applied
    )


def parse_args(argv: Optional[list[str]] = None) -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Fetch currency exchange rate for a given date from the local API and save as JSON."
    )
    parser.add_argument("-b", "--base", required=True, help="Base currency code, e.g., USD")
    parser.add_argument("-t", "--target", required=True, help="Target currency code, e.g., EUR")
    parser.add_argument("-d", "--date", required=True, help="Date in YYYY-MM-DD format")
    parser.add_argument("--api-url", default="http://localhost:8080/", help="API base URL (default: http://localhost:8080/)")
    parser.add_argument("--api-key", default=None, help="API key (overrides API_KEY env or .env). If omitted, will try environment variable API_KEY.")
    return parser.parse_args(argv)


def validate_currency(code: str) -> str:
    if not re.fullmatch(r"[A-Z]{3}", code):
        raise ValueError(f"Invalid currency code: '{code}'. Use 3 uppercase letters, e.g. USD.")
    return code


def parse_date(value: str) -> date:
    try:
        dt = datetime.strptime(value, "%Y-%m-%d").date()
    except ValueError:
        raise ValueError(f"Invalid date format: '{value}'. Expected YYYY-MM-DD.")
    # Validate allowed range per assignment
    start = date(2025, 1, 1)
    end = date(2025, 9, 15)
    if not (start <= dt <= end):
        raise ValueError(f"Date '{value}' is outside allowed range {start}..{end}.")
    return dt


def fetch_rate(base: str, target: str, for_date: date, api_url: str, api_key: str) -> Dict[str, Any]:
    """
    Calls the local API: GET params in URL (?from=...&to=...&date=...) and POSTs 'key' with API key.
    Returns parsed JSON; raises on HTTP or API errors.
    """
    url = api_url.rstrip("/") + "/"
    params = {"from": base, "to": target, "date": for_date.isoformat()}
    data = {"key": api_key}
    try:
        resp = requests.post(url, params=params, data=data, timeout=10)
    except requests.RequestException as e:
        raise RuntimeError(f"Network error contacting API at {url}: {e}") from e

    if resp.status_code != 200:
        raise RuntimeError(f"API returned HTTP {resp.status_code}: {resp.text[:200]}")

    try:
        payload = resp.json()
    except Exception as e:
        raise RuntimeError(f"API did not return valid JSON: {e}. Raw: {resp.text[:200]}") from e

    # API schema per README: {"error": "", "data": {...}} or error message inside "error"
    if isinstance(payload, dict) and payload.get("error"):
        raise RuntimeError(f"API error: {payload.get('error')}")

    if "data" not in payload:
        raise RuntimeError(f"Unexpected API response: {payload}")

    return payload


def save_json(base: str, target: str, for_date: date, payload: Dict[str, Any]) -> Path:
    DATA_DIR.mkdir(parents=True, exist_ok=True)
    filename = f"rate_{base}_{target}_{for_date.isoformat()}.json"
    path = DATA_DIR / filename
    enriched = {
        "request": {"from": base, "to": target, "date": for_date.isoformat(), "saved_at": datetime.utcnow().isoformat() + "Z"},
        "response": payload,
    }
    with path.open("w", encoding="utf-8") as f:
        json.dump(enriched, f, ensure_ascii=False, indent=2)
    return path


def main(argv: Optional[list[str]] = None) -> int:
    setup_logging()
    try:
        args = parse_args(argv)
        base = validate_currency(args.base.upper())
        target = validate_currency(args.target.upper())
        if base == target:
            raise ValueError("Base and target currencies must be different.")
        dt = parse_date(args.date)

        api_key = args.api_key or os.getenv("API_KEY")
        if not api_key:
            raise ValueError("API key is required. Pass --api-key or set environment variable API_KEY.")

        payload = fetch_rate(base, target, dt, args.api_url, api_key)
        out_path = save_json(base, target, dt, payload)
        print(f"Saved: {out_path.relative_to(PROJECT_ROOT)}")
        return 0

    except Exception as e:
        # Log to file and print a clean message
        logging.error("Error: %s", e, exc_info=True)
        print(f"[ERROR] {e}")
        return 1


if __name__ == "__main__":
    sys.exit(main())
