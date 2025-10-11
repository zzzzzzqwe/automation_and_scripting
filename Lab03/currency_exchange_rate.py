import argparse
import json
import logging
import sys
from datetime import datetime
from pathlib import Path

try:
    import requests
except ImportError:
    requests = None

DATA_DIR = Path("data")
ERROR_LOG = Path("error.log")
DEFAULT_API_URL = "http://localhost:8080/"

# Логирование ошибок в файл и вывод в консоль
logging.basicConfig(
    level=logging.ERROR,
    format="%(asctime)s %(levelname)s %(message)s",
    handlers=[
        logging.FileHandler(ERROR_LOG, encoding="utf-8"),
        logging.StreamHandler(sys.stderr)
    ],
)

# Проверка наличия папки data, если нет, создаём
def ensure_data_dir():
    if not DATA_DIR.exists():
        DATA_DIR.mkdir(parents=True, exist_ok=True)

# Проверка даты
def validate_date(d: str) -> bool:
    try:
        datetime.fromisoformat(d)
        return True
    except Exception:
        return False

# Сохраняем в JSON
def save_json(from_cur: str, to_cur: str, date_str: str, payload: dict):
    fname = f"{from_cur}_{to_cur}_{date_str}.json"
    path = DATA_DIR / fname
    with open(path, "w", encoding="utf-8") as f:
        json.dump(payload, f, ensure_ascii=False, indent=2)
    return path

# Функция для вызова API
def call_api(api_url: str, from_cur: str, to_cur: str, date_str: str, key: str) -> dict:
    if requests is None:
        raise RuntimeError("Требуется библиотека 'requests'. Установите: pip install requests")
    params = {"from": from_cur, "to": to_cur, "date": date_str}
    try:
        resp = requests.post(api_url, params=params, data={"key": key}, timeout=10)
    except Exception as e:
        raise RuntimeError(f"Сетевая ошибка при обращении к {api_url}: {e}")
    try:
        j = resp.json()
    except Exception:
        raise RuntimeError(f"Ответ не является JSON. HTTP {resp.status_code}: {resp.text!r}")
    if isinstance(j, dict) and j.get("error"):
        raise RuntimeError(f"API вернул ошибку: {j.get('error')}")
    return j

# Функция для парсинга аргументов из строки
def parse_args():
    p = argparse.ArgumentParser(description="Получить курс валюты и сохранить JSON")
    p.add_argument("--from", dest="from_cur", required=True, help="Валюта-источник, например USD")
    p.add_argument("--to", dest="to_cur", required=True, help="Валюта-назначение, например EUR")
    p.add_argument("--date", dest="date", required=True, help="Дата в формате YYYY-MM-DD")
    p.add_argument("--key", dest="key", required=True, help="API-ключ (отправляется в теле POST)")
    p.add_argument("--api-url", dest="api_url", default=DEFAULT_API_URL, help="URL API (по умолчанию http://localhost:8080/)")
    return p.parse_args()

def main():
    args = parse_args()
    frm = args.from_cur.strip().upper()
    to = args.to_cur.strip().upper()
    date_str = args.date.strip()
    key = args.key.strip()
    api_url = args.api_url.rstrip("/") + "/"

    if not validate_date(date_str):
        msg = f"Неверный формат даты: {date_str}. Ожидается YYYY-MM-DD."
        logging.error(msg)
        print("Ошибка:", msg)
        return 1

    ensure_data_dir()

    try:
        payload = call_api(api_url, frm, to, date_str, key)
    except Exception as e:
        logging.error("Ошибка при получении данных: %s", e)
        print("Ошибка при получении курса:", e)
        return 2

    try:
        path = save_json(frm, to, date_str, payload)
    except Exception as e:
        logging.error("Ошибка при сохранении файла: %s", e)
        print("Ошибка при сохранении файла:", e)
        return 3

    print(f"Успешно сохранено: {path}")
    return 0

if __name__ == "__main__":
    sys.exit(main())
