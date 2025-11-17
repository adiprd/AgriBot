import os
import requests

OPENROUTER_URL = "https://openrouter.ai/api/v1/chat/completions"
API_KEY = os.environ.get("OPENROUTER_API_KEY")

def call_openrouter(system_prompt, user_prompt, model=None):
    if not API_KEY:
        raise RuntimeError("OPENROUTER_API_KEY not set")

    # Force Indonesian output
    system_prompt = (
        "Kamu adalah AgriBot, asisten ahli pertanian. "
        "Jawablah semua pertanyaan dalam Bahasa Indonesia yang jelas dan mudah dipahami."
    )

    headers = {
        "Authorization": f"Bearer {API_KEY}",
        "Content-Type": "application/json",
    }

    payload = {
        "model": model or os.environ.get("OPENROUTER_MODEL", "gpt-4o-mini"),
        "messages": [
            {"role": "system", "content": system_prompt},
            {"role": "user", "content": user_prompt},
        ],
        "temperature": 0.2
    }

    r = requests.post(OPENROUTER_URL, json=payload, headers=headers)
    r.raise_for_status()
    data = r.json()
    return data["choices"][0]["message"]["content"]
