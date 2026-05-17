# Jicopa — Game Control System

Sistema web para gestão completa dos Jogos Internos (Jicopa) de uma escola: cadastro de turmas, alunos, esportes e calendário; registro de resultados, presença e penalidades durante as partidas; e geração de relatórios e súmulas em PDF.

O sistema substitui o controle via planilhas, permitindo que **administradores** montem o evento e que **professores** registrem dados em tempo real durante os jogos, a partir de qualquer navegador.

## Funcionalidades principais

- Cadastro e gestão de turmas, alunos, esportes e modalidades
- Montagem de chaveamento e calendário de partidas
- Registro de placar, presença e penalidades em tempo real
- Ranking ao vivo com atualização periódica
- Emissão de súmulas e boletins de desempenho em PDF
- Controle de acesso por perfil (Administrador e Professor)
- Trilha de auditoria das alterações sensíveis

## Stack

| Camada              | Tecnologia                          |
|---------------------|-------------------------------------|
| Backend             | Laravel 13 (PHP 8.3+)               |
| Bridge SPA          | Inertia.js 2                        |
| Frontend            | React 18 + TypeScript               |
| Estilização         | Tailwind CSS 4 + shadcn/ui          |
| Tabelas             | TanStack Table 8                    |
| Build               | Vite                                |
| Banco de dados      | MySQL 8.0+                          |
| Autenticação        | Laravel Breeze                      |
| Autorização         | spatie/laravel-permission           |
| Auditoria           | spatie/laravel-activitylog          |
| Geração de PDF      | barryvdh/laravel-dompdf             |

## Requisitos

- PHP 8.3 ou superior
- Composer 2.x
- Node.js 20.x LTS
- MySQL 8.0+ (ou MariaDB equivalente)

## Instalação

```bash
git clone git@github.com:V1kator/Jicopa_Game_Control_System.git
cd Jicopa_Game_Control_System

composer install
cp .env.example .env
php artisan key:generate

# Configure as credenciais do banco no arquivo .env, depois:
php artisan migrate

npm install
npm run build
```

Para popular o banco com dados de exemplo (esportes, turmas e alunos):

```bash
php artisan db:seed --class=JicopaPlanilhasSeeder
```

## Ambiente de desenvolvimento

```bash
composer dev
```

O comando acima inicia em paralelo: servidor PHP, fila de jobs, leitor de logs e Vite com hot reload.

Alternativamente, em terminais separados:

```bash
php artisan serve
npm run dev
```

## Testes

```bash
php artisan test
```

## Estrutura

```
.
├── app/                # Controllers, Models, Services, Policies
├── bootstrap/          # Bootstrap da aplicação
├── config/             # Arquivos de configuração
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── public/             # Document root
├── resources/
│   ├── css/
│   ├── js/             # React + TypeScript (Inertia pages)
│   └── views/          # Blade (layout raiz Inertia)
├── routes/
│   ├── web.php
│   └── auth.php
├── storage/
└── tests/
```

## Deploy

Compatível com hospedagem compartilhada (PHP 8.3+ e MySQL) e VPS. Em produção:

```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
npm ci
npm run build
```

## Licença

MIT.
