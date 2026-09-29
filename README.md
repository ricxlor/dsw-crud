# CRUD de Alunos — PHP + MySQL

Sistema web de cadastro de alunos com as quatro operações do CRUD: **INSERT, SELECT, UPDATE e DELETE**. Todas as consultas usam **Prepared Statements** (proteção contra SQL Injection) e todas as saídas passam por **`htmlspecialchars()`** (proteção contra XSS).

| Item | Descrição |
|---|---|
| Disciplina | Desenvolvimento de Sistemas para Web |
| Professor | Wendell Bento Geraldes |
| Câmpus | Luziânia — IFG |
| Atividade | Recuperação — CRUD com INSERT, SELECT, UPDATE e DELETE |
| Aluno | Samuel Henriques de Alarcão Fernandes |
| Matrícula | 20231080030094 |

## Tecnologias

- PHP 8.3 com a extensão **mysqli** (orientada a objetos)
- MySQL 8.4
- HTML5, CSS e JavaScript (apenas `confirm()`)
- Docker e Docker Compose (opcional, para rodar o ambiente)

## Estrutura do projeto

```
projeto/
├── conexao.php         → Conexão com o banco (mysqli OO + connect_errno)
├── index.php           → Cadastro, listagem e busca (SELECT com LIKE)
├── cadastrar.php       → INSERT
├── editar.php          → Carrega os dados do aluno no formulário de edição
├── atualizar.php       → UPDATE
├── excluir.php         → DELETE (somente via POST)
├── mensagem.php        → Exibe as mensagens de sucesso/erro
├── estilo.css          → Estilos da página
├── banco.sql           → Script de criação do banco e dados de exemplo
├── Dockerfile          → Imagem PHP + Apache com mysqli
├── docker-compose.yml  → Sobe PHP, MySQL e phpMyAdmin
└── README.md           → Esta documentação
```

## Banco de dados

Banco `crud_recuperacao`, tabela `alunos`:

| Campo | Tipo | Observação |
|---|---|---|
| `id` | INT | Chave primária, `AUTO_INCREMENT` |
| `nome` | VARCHAR(100) | Obrigatório |
| `email` | VARCHAR(100) | Obrigatório |
| `curso` | VARCHAR(100) | Obrigatório |
| `criado_em` | TIMESTAMP | Preenchido automaticamente |

O `banco.sql` cria o banco, a tabela e insere 5 alunos de exemplo.

## Como executar

### Opção 1 — Docker

Na pasta do projeto:

```bash
docker compose up -d
```

| Serviço | Endereço |
|---|---|
| Sistema | http://localhost:8080 |
| phpMyAdmin | http://localhost:8081 (usuário `root`, senha `root`) |

O `banco.sql` é executado automaticamente na primeira vez. Para apagar o banco e recriá-lo a partir do script:

```bash
docker compose down -v && docker compose up -d
```

### Opção 2 — XAMPP

1. Copie a pasta do projeto para `C:\xampp\htdocs\` (ou `/opt/lampp/htdocs/` no Linux).
2. Inicie o **Apache** e o **MySQL** no painel do XAMPP.
3. Abra http://localhost/phpmyadmin, vá em **Importar** e selecione o `banco.sql`.
4. No `conexao.php`, altere o host e a senha:
   ```php
   $host  = 'localhost';
   $senha = '';
   ```
5. Acesse http://localhost/nome-da-pasta/

## Funcionalidades

| Operação | Arquivo | Como funciona |
|---|---|---|
| **INSERT** | `cadastrar.php` | Formulário `POST` em `index.php`. Valida campos vazios e o formato do e-mail, depois executa `INSERT INTO alunos (nome, email, curso) VALUES (?, ?, ?)`. |
| **SELECT** | `index.php` | Lista os alunos e filtra por nome, e-mail ou curso com `WHERE nome LIKE ? OR email LIKE ? OR curso LIKE ?`. O termo é enviado como `%termo%`. Com a busca vazia, lista todos. |
| **UPDATE** | `editar.php` → `atualizar.php` | O botão **Editar** abre `editar.php?id=X`, que busca o aluno e preenche o formulário (atributo `value`). O `id` segue em um campo `hidden`. O `atualizar.php` confirma que o registro existe e executa `UPDATE alunos SET nome = ?, email = ?, curso = ? WHERE id = ?`. |
| **DELETE** | `excluir.php` | Cada linha tem um formulário `POST` com o `id` em campo `hidden` e `onsubmit="return confirm(...)"`. O `excluir.php` recusa requisições GET e executa `DELETE FROM alunos WHERE id = ?`. |

Após cada operação, o usuário é redirecionado para a listagem com uma **mensagem de sucesso ou erro**.

## Segurança e boas práticas

### SQL Injection — Prepared Statements
Todas as consultas (inclusive a listagem) usam `prepare()` + `bind_param()` + `execute()`. O SQL é enviado ao banco com `?` no lugar dos valores, e os dados vão separados, portanto nunca são interpretados como comando SQL.

```php
$stmt = $conn->prepare('DELETE FROM alunos WHERE id = ?');
$stmt->bind_param('i', $id); // i = inteiro, s = string
$stmt->execute();
```

### XSS — `htmlspecialchars()`
Todo dado exibido na página passa por `htmlspecialchars($valor, ENT_QUOTES, 'UTF-8')`, que converte `<`, `>`, `"`, `'` e `&` em entidades HTML. Assim, `<script>alert('hack')</script>` aparece como texto e não é executado.

### Exclusão somente via POST + `confirm()`
Requisições GET podem ser disparadas por links, pré-carregamento do navegador ou robôs de busca, e ficam no histórico. Por isso a exclusão só aceita POST e pede confirmação antes do envio.

### Outras práticas
- **Conexão orientada a objetos** com `new mysqli()` e tratamento de falha com `connect_errno`. O erro técnico vai para o log e o usuário vê uma mensagem genérica.
- `mysqli_report(MYSQLI_REPORT_OFF)`: desde o PHP 8.1 o mysqli lança exceções por padrão. Desligar esse comportamento permite tratar os erros com `connect_errno` e com o retorno de `execute()`.
- `set_charset('utf8mb4')` para acentuação correta.
- **Validação no servidor** (campos vazios com `trim()`, e-mail com `filter_var`). O `required` do HTML sozinho pode ser burlado.
- IDs convertidos para inteiro com `filter_input(..., FILTER_VALIDATE_INT)`.
- **Registro inexistente:** `editar.php`, `atualizar.php` e `excluir.php` exibem "Registro não encontrado". Na exclusão, isso é verificado com `affected_rows`.
- **Formulários semânticos:** todo campo tem `<label for="...">` ligado ao `id` do input, além do atributo `name`.

## Testes realizados

| Teste | Resultado esperado |
|---|---|
| Cadastrar aluno com dados válidos | "Aluno cadastrado com sucesso!" |
| Cadastrar com campo vazio / e-mail inválido | Mensagem de erro |
| Buscar "Sistemas" | Somente os alunos desse curso |
| Buscar `' OR '1'='1` | "Nenhum registro encontrado" (injeção não funciona) |
| Cadastrar nome `<script>alert('hack')</script>` | Exibido como texto, sem executar |
| Editar aluno | Formulário vem preenchido, alteração é salva |
| Salvar edição sem mudar nada | "Nenhuma alteração foi feita." |
| Abrir `editar.php?id=9999` | "Registro não encontrado." |
| Excluir e clicar em **Cancelar** no `confirm()` | Nada é excluído |
| Excluir e confirmar | "Aluno excluído com sucesso!" |
| Acessar `excluir.php?id=1` pela URL (GET) | "Método não permitido para exclusão." |
