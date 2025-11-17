# Agri Chatbot

Sistem chatbot pintar untuk bidang pertanian yang menggabungkan teknologi RAG (Retrieval-Augmented Generation) dengan backend FastAPI dan frontend PHP.

## Gambaran Umum

Agri Chatbot adalah platform asisten virtual yang dirancang khusus untuk memberikan informasi dan solusi dalam bidang pertanian. Sistem ini memanfaatkan teknologi AI modern dengan arsitektur RAG untuk menyediakan jawaban yang akurat dan kontekstual berdasarkan pengetahuan pertanian yang terstruktur.

## Fitur Utama

- **Sistem RAG (Retrieval-Augmented Generation)**: Pencarian dokumen cerdas menggunakan FAISS untuk menemukan informasi relevan
- **Backend FastAPI**: API RESTful berkinerja tinggi dengan dokumentasi otomatis
- **Integrasi OpenRouter**: Akses ke berbagai model bahasa besar (LLM) terkemuka
- **Frontend PHP**: Antarmuka web yang ringan dan mudah diintegrasikan
- **Docker Container**: Deployment yang konsisten dan mudah direplikasi
- **Manajemen Dokumen**: Kemampuan untuk menambahkan dan mengindeks dokumen pengetahuan pertanian

## Arsitektur Sistem

```
agri_chatbot/
├── backend/
│   ├── app/
│   │   ├── main.py              # Aplikasi FastAPI utama
│   │   ├── router.py            # Definisi endpoint API
│   │   ├── llm_client.py        # Klien untuk OpenRouter API
│   │   ├── retriever.py         # Sistem RAG dengan FAISS
│   │   └── requirements.txt     # Dependencies Python
│   ├── .env.example            # Template konfigurasi environment
│   └── Dockerfile              # Konfigurasi container backend
└── frontend/
    └── index.php               # Antarmuka web PHP
```

## Teknologi yang Digunakan

### Backend
- **FastAPI**: Framework web modern untuk Python
- **FAISS**: Library untuk similarity search dan clustering
- **Sentence Transformers**: Model embedding untuk representasi teks
- **OpenRouter API**: Gateway untuk berbagai model LLM
- **Uvicorn**: ASGI server untuk menjalankan FastAPI

### Frontend
- **PHP**: Server-side scripting untuk antarmuka web
- **HTML**: Struktur dasar halaman web
- **cURL**: Untuk komunikasi dengan backend API

## Persyaratan Sistem

### Untuk Development
- Python 3.11+
- PHP 7.4+
- Docker (opsional, untuk containerization)

### Dependencies Python
- fastapi
- uvicorn[standard]
- requests
- python-dotenv
- faiss-cpu
- sentence-transformers
- pydantic

## Instalasi dan Setup

### 1. Clone dan Setup Struktur Project
```bash
# Jalankan script untuk membuat struktur project
python create_project.py
```

### 2. Setup Backend

#### Konfigurasi Environment
```bash
cd agri_chatbot/backend
cp .env.example .env
# Edit file .env dengan API key OpenRouter Anda
```

#### Install Dependencies
```bash
cd app
pip install -r requirements.txt
```

#### Menjalankan Backend (Development)
```bash
uvicorn main:app --reload --host 0.0.0.0 --port 8000
```

### 3. Setup Frontend

#### Menjalankan PHP Server
```bash
cd agri_chatbot/frontend
php -S localhost:8080
```

### 4. Menggunakan Docker (Opsional)

#### Build dan Jalankan Backend
```bash
cd agri_chatbot/backend
docker build -t agri-chatbot-backend .
docker run -p 8000:8000 --env-file .env agri-chatbot-backend
```

## Konfigurasi

### Environment Variables
Buat file `.env` di folder backend dengan konfigurasi berikut:

```env
OPENROUTER_API_KEY=your_openrouter_api_key_here
OPENROUTER_MODEL=gpt-4o-mini
```

### Model yang Didukung
Sistem mendukung berbagai model melalui OpenRouter:
- gpt-4o-mini (default)
- gpt-4o
- claude-3-sonnet
- dan model lainnya yang didukung OpenRouter

## Penggunaan

### 1. Melalui Frontend Web
Akses antarmuka web di `http://localhost:8080` dan gunakan form chat untuk berinteraksi dengan bot.

### 2. Melalui API Langsung

#### Endpoint Chat
```bash
curl -X POST "http://localhost:8000/api/chat" \
     -H "Content-Type: application/json" \
     -d '{"message": "Bagaimana cara mengatasi hama pada tanaman padi?", "use_rag": true}'
```

#### Endpoint Ingest (Menambah Dokumen)
```bash
curl -X POST "http://localhost:8000/api/ingest" \
     -H "Content-Type: application/json" \
     -d '{"id": "doc1", "text": "Informasi tentang budidaya padi organik..."}'
```

### 3. Menambahkan Dokumen Pengetahuan

Gunakan endpoint `/api/ingest` untuk menambahkan dokumen pengetahuan pertanian ke dalam sistem:

```python
# Contoh menambahkan dokumen
documents = [
    {
        "id": "pest_control",
        "text": "Pengendalian hama terpadu meliputi monitoring rutin, penggunaan pestisida alami, dan rotasi tanaman."
    },
    {
        "id": "organic_farming", 
        "text": "Pertanian organik menghindari penggunaan pupuk kimia dan pestisida sintetik, fokus pada kesuburan tanah alami."
    }
]
```

## API Documentation

### Endpoints

#### POST /api/chat
Mengirim pesan ke chatbot.

**Request Body:**
```json
{
  "message": "string",
  "use_rag": boolean
}
```

**Response:**
```json
{
  "reply": "string"
}
```

#### POST /api/ingest
Menambahkan dokumen ke knowledge base.

**Request Body:**
```json
{
  "id": "string",
  "text": "string"
}
```

**Response:**
```json
{
  "status": "ok"
}
```

## Customization

### Menyesuaikan Model Embedding
Edit file `retriever.py` untuk mengubah model embedding:

```python
MODEL = "sentence-transformers/all-mpnet-base-v2"  # Model yang lebih besar
```

### Menyesuaikan Prompt System
Edit file `router.py` untuk mengubah system prompt:

```python
system_prompt = "Anda adalah asisten ahli pertanian dengan spesialisasi dalam tanaman pangan."
```

### Konfigurasi Retrieval
Ubah parameter retrieval di file `router.py`:

```python
hits = retr.retrieve(req.message, 5)  # Mengambil 5 dokumen teratas
```

## Troubleshooting

### Masalah Umum

1. **API Key Tidak Valid**
   - Pastikan OPENROUTER_API_KEY sudah di-set dengan benar
   - Verifikasi kredit API di dashboard OpenRouter

2. **Backend Tidak Dapat Diakses**
   - Pastikan backend berjalan di port 8000
   - Periksa firewall dan setting network

3. **Dokumen Tidak Terindeks**
   - Pastikan path untuk FAISS index writable
   - Periksa permission folder

4. **Response Lambat**
   - Kurangi jumlah dokumen yang di-retrieve
   - Pertimbangkan menggunakan model embedding yang lebih kecil

### Logging dan Debug

Aktifkan debug mode dengan menambahkan environment variable:
```env
DEBUG=true
```

## Pengembangan Lanjutan

### Fitur yang Dapat Ditambahkan

1. **Authentication**: Menambahkan sistem autentikasi untuk API
2. **Database**: Penyimpanan history chat dan dokumen
3. **Multiple Knowledge Bases**: Kategori pengetahuan yang berbeda
4. **File Upload**: Support upload dokumen PDF, DOCX
5. **Web Interface yang Lebih Kaya**: Frontend dengan JavaScript framework
6. **Monitoring**: Metrics dan monitoring performa sistem

### Optimasi Performa

1. **Caching**: Implementasi caching untuk response yang sering digunakan
2. **Batch Processing**: Processing dokumen dalam batch untuk ingest besar
3. **Async Operations**: Menggunakan async/await untuk operasi I/O
4. **Load Balancing**: Multiple instances untuk handle traffic tinggi

## Kontribusi

Kontribusi untuk pengembangan Agri Chatbot sangat diterima. Beberapa area yang dapat dikembangkan:

- Integrasi dengan database pertanian eksternal
- Support untuk bahasa daerah
- Fitur image recognition untuk penyakit tanaman
- Integrasi dengan sensor IoT pertanian

## Lisensi

Project ini dilisensikan di bawah MIT License.

## Support

Untuk pertanyaan teknis dan bantuan pengembangan, silakan buat issue di repository project atau hubungi tim pengembang.

## Catatan Versi

### v1.0.0
- Implementasi dasar RAG system
- Integrasi dengan OpenRouter API
- Frontend PHP sederhana
- Docker support
- FAISS untuk vector storage
