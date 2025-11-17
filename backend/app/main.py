
from fastapi import FastAPI
from app.apirouter import router

app = FastAPI()
app.include_router(router, prefix="/api")
