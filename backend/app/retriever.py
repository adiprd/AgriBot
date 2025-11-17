
import os
import pickle
import faiss
from sentence_transformers import SentenceTransformer

INDEX_PATH = "./faiss.index"
MAP_PATH = "./docmap.pkl"
MODEL = "sentence-transformers/all-MiniLM-L6-v2"

class Retriever:
    def __init__(self):
        self.model = SentenceTransformer(MODEL)
        self.index = None
        self.docmap = {}

        if os.path.exists(INDEX_PATH) and os.path.exists(MAP_PATH):
            self.index = faiss.read_index(INDEX_PATH)
            with open(MAP_PATH, "rb") as f:
                self.docmap = pickle.load(f)

    def save(self):
        faiss.write_index(self.index, INDEX_PATH)
        with open(MAP_PATH, "wb") as f:
            pickle.dump(self.docmap, f)

    def ingest(self, docs):
        texts = [d["text"] for d in docs]
        embs = self.model.encode(texts)

        if self.index is None:
            self.index = faiss.IndexFlatIP(embs.shape[1])

        self.index.add(embs)
        base = len(self.docmap)

        for i, d in enumerate(docs):
            self.docmap[base + i] = d

        self.save()

    def retrieve(self, q, k=3):
        if self.index is None:
            return []
        qemb = self.model.encode([q])
        scores, idxs = self.index.search(qemb, k)
        out = []
        for score, idx in zip(scores[0], idxs[0]):
            if idx in self.docmap:
                out.append(self.docmap[idx])
        return out
