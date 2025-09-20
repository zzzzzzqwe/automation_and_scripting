# Лабораторная работа 2
# Студент: Gachayev Dmitrii, I2302
# Дата выполнения: 20.09.2025

Данный скрипт позволяет получать курс валют через предоставленный сервис (lab02prep) и сохранять результаты в файлы JSON.

---

## Установка зависимостей

Скрипт написан на Python и требует установленной библиотеки `requests`.

1. Убедитесь, что у вас установлен Python :

```bash
python --version
```

Если нет, его необходимо скачать с официального сайта (https://www.python.org/downloads/)[https://www.python.org/downloads/]

2. Установите библиотеку `requests`:

```bash
pip install requests
```

---

## Запуск скрипта

Скрипт запускается из терминала с указанием валют, даты, API-ключа и API-url.

### Пример команды:

```bash
python currency_exchange_rate.py --from USD --to EUR --date 2025-09-01 --key EXAMPLEAPIKEY
```

### Пояснение параметров:

* `--from` — валюта-источник (например USD)
* `--to` — валюта-приёмник (например EUR)
* `--date` — дата в формате `YYYY-MM-DD`
* `--key` — API-ключ для доступа к сервису, который определён в `.env` файле сервиса
* `--api-url` *(необязательный)* — URL API (по умолчанию `http://localhost:8080/`)

После успешного выполнения скрипт создаст файл в папке `data` с именем вида:

```
data/USD_EUR_2025-09-01.json
```

---

## Как устроен скрипт

Скрипт содержит несколько основных частей:

1. **Разбор аргументов командной строки (`parse_args`)**
```python
def parse_args():
    p = argparse.ArgumentParser(description="Получить курс валюты и сохранить JSON")
    p.add_argument("--from", dest="from_cur", required=True, help="Валюта-источник, например USD")
    p.add_argument("--to", dest="to_cur", required=True, help="Валюта-назначение, например EUR")
    p.add_argument("--date", dest="date", required=True, help="Дата в формате YYYY-MM-DD")
    p.add_argument("--key", dest="key", required=True, help="API-ключ (отправляется в теле POST)")
    p.add_argument("--api-url", dest="api_url", default=DEFAULT_API_URL, help="URL API (по умолчанию http://localhost:8080/)")
    return p.parse_args()
```
   С помощью этого метода считываются параметры `--from`, `--to`, `--date`, `--key` и `--api-url`.

2. **Проверка и создание директории `data` (`ensure_data_dir`)**
```python
def ensure_data_dir():
    if not DATA_DIR.exists():
        DATA_DIR.mkdir(parents=True, exist_ok=True)
```
   Перед сохранением JSON-ответа скрипт создаёт папку `data`, если её нет.

3. **Проверка формата даты (`validate_date`)**
```python
def validate_date(d: str) -> bool:
    try:
        datetime.fromisoformat(d)
        return True
    except Exception:
        return False
```
   Скрипт проверяет, что дата передана в формате `YYYY-MM-DD`.

4. **Вызов API (`call_api`)**
```python
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

```
   Отправляется POST-запрос к сервису с параметрами `from`, `to`, `date` и API-ключом.
   Скрипт обрабатывает сетевые ошибки, неверный JSON и ошибки, возвращаемые самим API.

5. **Сохранение ответа в файл JSON (`save_json`)**
```python
def save_json(from_cur: str, to_cur: str, date_str: str, payload: dict):
    fname = f"{from_cur}_{to_cur}_{date_str}.json"
    path = DATA_DIR / fname
    with open(path, "w", encoding="utf-8") as f:
        json.dump(payload, f, ensure_ascii=False, indent=2)
    return path
```
   Полученный JSON сохраняется в файл `data/FROM_TO_DATE.json`.
   В случае ошибки при сохранении запись логируется в `error.log`.

6. **Логирование ошибок**
   Все ошибки записываются в `error.log` и выводятся в консоль, чтобы было понятно, что пошло не так.
```
2025-09-20 18:55:30,275 ERROR Не удалось получить курс для USD->EUR на 2025-09-01: API error: Invalid API key
2025-09-20 18:55:47,713 ERROR API вернул ошибку: Invalid API key
2025-09-20 19:02:22,274 ERROR Ошибка при получении данных: API вернул ошибку: Invalid API key
2025-09-20 19:02:40,077 ERROR Ошибка при получении данных: API вернул ошибку: The currency  is unknown
```

---

## Пример вывода

После выполнения команды:

```bash
python currency_exchange_rate.py --from USD --to EUR --date 2025-09-01 --key DMITRIIGACHAYEV --api-url http://localhost:8080/
```

В консоли появится:

```
Успешно сохранено: data\USD_EUR_2025-09-01.json
```

Файл JSON будет содержать:

```json
{
  "error": "",
  "data": {
    "from": "USD",
    "to": "EUR",
    "rate": 1.1667007309800903,
    "date": "2025-09-01"
  }
}
```

---
