FROM python:3.11-slim

# Instala dependências de sistema (se necessário)
RUN pip install --upgrade pip

WORKDIR /app

COPY requirements.txt /app/
RUN pip install -r requirements.txt

COPY . /app

# Expõe porta 8001
EXPOSE 8001

CMD ["python", "app.py"]
