## Subindo a aplicação

Clone este repositório, acesse a pasta onde foi clonado e suba os contêineres da aplicação executando:

```bash
docker-compose up -d
```

Será necessário criar uma nova base limpa com o modelo desta aplicação, em seguida realizar as migrations para criar as tabelas.

Criando a base:
```bash
docker-compose exec php php bin/console doctrine:database:create
```

Criando as tabelas:
```bash
docker-compose exec php php bin/console doctrine:migrations:migrate
```

Isto deve ser o suficiente e basta acessar o endereço <http://localhost:8080>.
