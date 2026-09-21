# Bora+ — Baladas, bares e eventos em Curitiba

Site minimalista para descobrir onde sair em Curitiba. O admin cadastra **locais**
(bar, balada, festa, casa de show, pub) e depois adiciona **eventos** a eles.

## Como rodar

Requisitos: **PHP 8.0+** com as extensões `pdo_sqlite` e `fileinfo` (já vêm ativas no XAMPP).

**Opção 1 — XAMPP**
1. Copie esta pasta para `C:\xampp\htdocs\`.
2. Inicie o Apache no painel do XAMPP.
3. Acesse `http://localhost/Bora+/`.

**Opção 2 — servidor embutido do PHP**
```
php -S localhost:8000
```
Acesse `http://localhost:8000/`. (Nesse modo o `.htaccess` não vale; use só para desenvolvimento.)

O banco SQLite (`database/bora.sqlite`) é criado sozinho na primeira visita, já com
alguns locais e eventos de exemplo (fictícios) que você pode apagar pelo painel.

## Painel admin

- URL: `/admin/`
- E-mail: `admin@bora.com`
- Senha: `admin123`

**Troque a senha** em *Admin → Conta* no primeiro acesso.

Fluxo: **Locais → Novo local** (nome do bar/festa, tipo, bairro…) → **Novo evento**
(escolhe o local, data, horário, entrada…).

## Login com Google e outras redes

A tela de login tem botões de **Google e Facebook**. Para ativar cada
um você precisa criar as chaves (grátis) no painel de desenvolvedor da rede e colar em
`includes/oauth_config.php`:

1. **Autorize seu e-mail**: em `allowed_emails`, coloque os e-mails que podem entrar no admin.
   Só eles conseguem entrar por login social (se a lista estiver vazia, o login social fica
   bloqueado). Na primeira entrada o administrador é criado automaticamente.
2. **Crie o app** na rede escolhida e cadastre a **URI de redirecionamento** (é a mesma para
   todas as redes; ela aparece também na mensagem que o botão mostra enquanto não está configurado):
   - com `php -S localhost:8000`: `http://localhost:8000/admin/oauth_callback.php`
   - com XAMPP: `http://localhost/NOME_DA_PASTA/admin/oauth_callback.php`
     (dica: renomeie a pasta `Bora+` para `bora`, pois o `+` complica a URI)
3. **Cole o Client ID e o Client Secret** da rede em `includes/oauth_config.php`.

| Rede | Onde criar | Observação |
|------|------------|------------|
| Google | console.cloud.google.com → APIs e serviços → Credenciais → ID do cliente OAuth (Aplicativo da Web) | Configure a tela de consentimento |
| Facebook | developers.facebook.com → Criar app (Login do Facebook) | O app precisa da permissão `email` |

Requer a extensão **cURL** do PHP (já ativa no XAMPP). Em produção use HTTPS e não publique
o arquivo `oauth_config.php` com as chaves.

## Estrutura

```
Bora+/
├── index.php            Home: busca, filtros, eventos e lugares
├── evento.php           Página de um evento
├── local.php            Página de um local + próximos eventos
├── admin/
│   ├── index.php        Painel
│   ├── login.php / logout.php
│   ├── oauth_start.php / oauth_callback.php   Login social
│   ├── locais.php       Lista de locais (excluir)
│   ├── local_form.php   Criar/editar local
│   ├── eventos.php      Lista de eventos (excluir)
│   ├── evento_form.php  Criar/editar evento
│   └── conta.php        E-mail e senha do admin
├── includes/            Núcleo em PHP (não acessível pelo navegador)
│   ├── config.php       Constantes, tipos de local, bairros sugeridos
│   ├── db.php           Conexão SQLite, tabelas e dados de exemplo
│   ├── auth.php         Sessão, login e CSRF
│   ├── oauth.php        Lógica do login social
│   ├── oauth_config.php Chaves das redes e e-mails autorizados
│   ├── functions.php    Helpers (escape, datas, upload…)
│   ├── queries.php      Consultas ao banco
│   ├── components.php   Cards reutilizáveis
│   └── header/footer (público e admin)
├── assets/
│   ├── css/             style.css (site) e admin.css (painel)
│   └── js/              main.js e admin.js
├── uploads/             Imagens enviadas pelo admin
└── database/            Arquivo SQLite (bloqueado via .htaccess)
```

## Segurança

- Consultas com *prepared statements* (PDO) e saída escapada.
- Token CSRF em todos os formulários; senhas com `password_hash`.
- Upload validado por tipo real (JPG/PNG/WebP) e limite de 4 MB; `uploads/` não executa PHP.
- Em produção, mantenha o Apache com `AllowOverride All` para que os `.htaccess` funcionem
  e use HTTPS.
