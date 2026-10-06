# Painel CMS — Base em PHP e MySQL

Base do painel administrativo para o **Projeto Prático: CMS com PHP e MySQL (Parte 2)**.

Já vêm prontos e funcionando:

- **Autenticação**: login, logout, "Manter conectado", proteção das páginas e perfis de acesso (Administrador e Editor);
- **CRUD de usuários**: listar (com busca, filtros e paginação), cadastrar, editar e excluir, com upload de foto e editor rich text;
- **Banco de dados**: script SQL com as tabelas e o administrador inicial.

Sua tarefa é construir, **seguindo o mesmo padrão**, os CRUDs do tema da sua dupla.

---

## Como instalar

1. Copie a pasta `cms-php` para dentro de `htdocs` (XAMPP) ou `www` (Laragon/WAMP).
2. Inicie o **Apache** e o **MySQL**.
3. Abra o phpMyAdmin (`http://localhost/phpmyadmin`), clique em **Importar** e envie o arquivo `sql/banco.sql`. Ele cria o banco `cms`, as tabelas e o administrador.
4. *(Opcional)* Importe também `sql/dados-exemplo.sql` para ter usuários fictícios e testar a busca e a paginação.
5. Confira os dados de conexão em `config/conexao.php` (o padrão é usuário `root` sem senha).
6. Acesse `http://localhost/cms-php/`.

| Perfil | E-mail | Senha |
|---|---|---|
| Administrador | `admin@cms.com` | `123456` |
| Editor *(só com os dados de exemplo)* | `editor@cms.com` | `123456` |

> Requisitos: PHP 7.4 ou superior e MySQL 5.7+ / MariaDB 10+. Troque a senha do administrador depois do primeiro acesso.

---

## Estrutura de pastas

```
cms-php/
├── index.php                   → Tela de login
├── login.php                   → Confere e-mail e senha e cria a sessão
├── logout.php                  → Encerra a sessão
├── config/
│   └── conexao.php             → Conexão com o banco (PDO) → variável $pdo
├── includes/
│   ├── sessao.php              → Inicia a sessão + funções de login/logout
│   ├── auth.php                → Protege as páginas do painel
│   ├── funcoes.php             → Funções gerais (escape, mensagens, CSRF, upload...)
│   ├── header.php              → Início do layout (<head>, menu, barra superior)
│   ├── sidebar.php             → Menu lateral
│   ├── footer.php              → Rodapé e scripts
│   ├── usuarios-funcoes.php    → Leitura e validação do formulário de usuário
│   └── usuario-form.php        → HTML do formulário (usado no cadastro e na edição)
├── pages/
│   ├── dashboard.php           → Página inicial do painel
│   ├── usuarios.php            → Listagem          (Read)
│   ├── usuarios-cadastrar.php  → Cadastro          (Create)
│   ├── usuarios-editar.php     → Edição            (Update)
│   └── usuarios-excluir.php    → Exclusão          (Delete)
├── sql/
│   ├── banco.sql               → Tabelas + administrador inicial
│   └── dados-exemplo.sql       → Usuários fictícios (opcional)
├── uploads/usuarios/           → Fotos enviadas pelo formulário
├── css/  js/  img/             → Arquivos do template (front-end)
```

---

## Banco de dados

| Tabela | Para que serve |
|---|---|
| `usuarios` | Quem pode acessar o painel. A senha é guardada como *hash* (`password_hash`), o e-mail e o nome de usuário são `UNIQUE`. |
| `usuarios_tokens` | Tokens da opção "Manter conectado". Tem chave estrangeira para `usuarios` com `ON DELETE CASCADE`. |

Ao criar as tabelas do seu tema, você pode ligar o conteúdo ao autor:

```sql
CREATE TABLE noticias (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  titulo     VARCHAR(150) NOT NULL,
  conteudo   MEDIUMTEXT   NOT NULL,              -- HTML do editor rich text
  usuario_id INT UNSIGNED NOT NULL,              -- quem escreveu
  criado_em  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

> Com essa chave estrangeira, o MySQL não deixa excluir um usuário que tenha notícias. O painel já trata esse caso e sugere desativar o usuário.

Na entrega, gere o dump `.sql` pelo phpMyAdmin (**Exportar**) com todas as tabelas, inclusive as duas acima.

---

## Como a autenticação funciona

1. `index.php` exibe o formulário, que é enviado para `login.php`.
2. `login.php` busca o usuário pelo e-mail com *prepared statement*, confere a senha com `password_verify()` e grava `$_SESSION['usuario_id']`.
3. Toda página do painel começa com `require_once '../includes/auth.php';`. Esse arquivo manda para o login quem não tem sessão e busca no banco os dados de quem está logado — por isso um usuário desativado ou excluído perde o acesso na hora.
4. `logout.php` destrói a sessão.

Depois do `auth.php`, ficam disponíveis:

| Item | O que é |
|---|---|
| `$pdo` | Conexão com o banco |
| `$usuarioLogado` | Array com `id`, `nome`, `email`, `usuario`, `foto`, `perfil` e `status` de quem está logado |
| `ehAdmin()` | `true` se o usuário logado é administrador |
| `exigirAdmin()` | Bloqueia a página para quem não é administrador |

**Perfis:** o *Administrador* acessa tudo. O *Editor* não vê o menu Usuários e só edita o próprio cadastro ("Meu perfil").

---

## Criando o CRUD do seu tema

Use os arquivos de usuários como modelo. Para uma entidade `noticias`, por exemplo:

| Arquivo novo | Copie de | O que muda |
|---|---|---|
| `pages/noticias.php` | `usuarios.php` | `SELECT` da sua tabela e colunas da listagem |
| `pages/noticias-cadastrar.php` | `usuarios-cadastrar.php` | Campos e `INSERT` |
| `pages/noticias-editar.php` | `usuarios-editar.php` | `SELECT` por id e `UPDATE` |
| `pages/noticias-excluir.php` | `usuarios-excluir.php` | `DELETE` |
| `includes/noticias-funcoes.php` | `usuarios-funcoes.php` | Leitura e validação dos seus campos |
| `includes/noticia-form.php` | `usuario-form.php` | HTML do seu formulário |

Depois, adicione o link no menu em `includes/sidebar.php`.

Esqueleto de qualquer página do painel:

```php
<?php
require_once '../includes/auth.php';   // protege a página
require_once '../config/conexao.php';

// ... consultas e processamento do formulário ...

$titulo    = 'Notícias';   // título da aba
$menuAtivo = 'noticias';   // item destacado no menu
require_once '../includes/header.php';
?>
<main class="content">
  <?php exibirMensagem(); ?>
  <!-- conteúdo da página -->
</main>
<?php require_once '../includes/footer.php'; ?>
```

### Funções prontas (`includes/funcoes.php`)

| Função | Uso |
|---|---|
| `e($texto)` | Escapa o texto para exibir no HTML (`htmlspecialchars`) |
| `post('campo')` / `get('campo')` | Lê `$_POST` / `$_GET` como texto, sem espaços nas pontas |
| `redirecionar('pagina.php')` | `header('Location: ...')` + `exit` |
| `definirMensagem('Texto', 'success')` | Guarda a mensagem na sessão para a próxima página |
| `exibirMensagem()` | Mostra o `.alert` com a mensagem e a apaga da sessão |
| `csrfCampo()` / `csrfValidar()` | Campo oculto de segurança nos formulários POST e sua conferência |
| `erroClasse($erros, 'campo')` / `erroTexto(...)` | Destacam o campo com erro de validação |
| `salvarImagem($_FILES['foto'], 'pasta', $erro)` | Valida e salva uma imagem em `uploads/pasta`; devolve o caminho |
| `apagarImagem($caminho)` | Apaga o arquivo salvo |
| `limparHtml($html)` | Remove código perigoso do HTML do editor rich text |
| `formatarData($data)` | `2026-12-31 14:30:00` → `31/12/2026` |

### Regras obrigatórias

- **Sempre** use *prepared statements* (`$pdo->prepare()` + `execute()`); nunca concatene variáveis no SQL.
- Exiba os dados do banco com `e()`. A única exceção é o HTML do editor rich text, que deve passar por `limparHtml()` **antes de ser gravado**.
- Valide tudo no PHP, mesmo que já exista validação no JavaScript.
- Páginas que alteram dados (cadastrar, editar, excluir) recebem **POST** e conferem o `csrfValidar()`.
- Depois de gravar, use `definirMensagem()` + `redirecionar()` (evita gravar de novo ao apertar F5).

---

## Componentes visuais

Todos estão em `css/style.css` e podem ser reaproveitados:

| Componente | Classes |
|---|---|
| Botões | `btn btn-primary`, `btn-light`, `btn-danger`, `btn-ghost`, `btn-sm`, `btn-lg` |
| Cards | `card`, `card__header`, `card__body`, `card__footer` |
| Formulário | `form-grid` + `col-12 / col-6 / col-4`, `form-group`, `form-label`, `form-control`, `form-select` |
| Validação | `has-error` no `form-group` + `<div class="invalid-feedback">` |
| Tabela | `table`, `table--stack` (vira cartões no celular; use `data-label` nos `<td>`) |
| Badges | `badge badge-success`, `badge-danger`, `badge-primary`, `badge-gray`, `badge-dot` |
| Alertas | `alert alert-success` / `alert-danger` / `alert-warning` / `alert-info` |
| Modal | `<div class="modal" id="...">` + botão com `data-modal-open="#id"` |
| Toast (JS) | `CMS.toast('Mensagem', 'success')` |

Para mudar as cores do painel, altere as variáveis no início de `style.css` (`--primary-600`, etc.).

---

## Editor rich text (Summernote)

O campo **Biografia** usa o [Summernote Lite](https://summernote.org/), que transforma um `<textarea>` em editor visual e envia **HTML** no POST.

Para usar em outro formulário (ex.: corpo de uma notícia):

1. Na página, defina `$usaEditor = true;` antes do `header.php` (carrega o CSS e o JS do editor).
2. No seu JavaScript: `$('#conteudo').summernote({ lang: 'pt-BR', height: 260 });`
3. No PHP: `$conteudo = limparHtml(post('conteudo'));`
4. No MySQL, use uma coluna `MEDIUMTEXT` (as imagens inseridas no editor ficam em Base64 dentro do HTML).
5. Ao preencher o `<textarea>` na edição, use `e()`: `<textarea><?= e($conteudo) ?></textarea>`.
6. No site público, exiba o conteúdo **sem** `e()` — ele já foi limpo ao ser gravado.

---

## Bibliotecas utilizadas

- [jQuery 3.7.1](https://jquery.com/)
- [Summernote Lite 0.8.20](https://summernote.org/) — editor rich text
- [Bootstrap Icons 1.11](https://icons.getbootstrap.com/) — ícones (`<i class="bi bi-nome-do-icone"></i>`)
- [Inter](https://rsms.me/inter/) — fonte

Tudo funciona **sem internet**: as bibliotecas estão nas pastas `vendor`.
