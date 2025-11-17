
from fastapi import APIRouter
from pydantic import BaseModel
from app.llm_client import call_openrouter
from app.retriever import Retriever

router = APIRouter()
retr = Retriever()

class ChatReq(BaseModel):
    message: str
    use_rag: bool = True

@router.post("/chat")
def chat(req: ChatReq):
    context = ""
    if req.use_rag:
        hits = retr.retrieve(req.message, 3)
        for h in hits:
            context += f"Context: {h['text']}\n\n"

    system_prompt = "You are an agriculture expert assistant."
    user_prompt = context + req.message

    reply = call_openrouter(system_prompt, user_prompt)
    return {"reply": reply}

class IngestReq(BaseModel):
    id: str
    text: str

@router.post("/ingest")
def ingest(req: IngestReq):
    retr.ingest([{"id": req.id, "text": req.text}])
    return {"status": "ok"}
