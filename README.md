# service-store-banco-indicadores
Service Store for Banco de Indicadores Hopitalares

Build do container Docker

```
docker build -f Dockerfile -t hersonpc/service-store-banco-indicadores .
```

Executando o container manualmente

```
docker run -it --rm --name sst --env-file .env -p 8077:80 hersonpc/service-store-banco-indicadores
```

Executando via docker-compose

```
docker-compose up
```
