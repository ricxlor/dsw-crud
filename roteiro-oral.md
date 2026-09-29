# Roteiro da Apresentação Oral — 02/10/2026

> Arquivo de estudo pessoal, **não faz parte da entrega**.
> Como usar: leia a pergunta, tente responder **em voz alta sem olhar**, depois confira.
> As respostas estão em linguagem falada. Use as suas palavras, não decore frase por frase.

---

## Checklist do dia

- [ ] Notebook carregado + carregador na mochila
- [ ] Testar **sem internet**: `docker compose up -d` e abrir http://localhost:8080
- [ ] Banco "limpo" (5 alunos de exemplo): `docker compose down -v && docker compose up -d`
- [ ] Editor aberto com os 6 arquivos em abas, **na ordem**: `conexao.php`, `index.php`, `cadastrar.php`, `editar.php`, `atualizar.php`, `excluir.php`
- [ ] Fonte do editor aumentada (Ctrl + `+`) para o professor enxergar
- [ ] Números de linha visíveis no editor
- [ ] Plano B: se o computador for do laboratório com XAMPP, levar o projeto num pendrive e seguir a "Opção 2" do README

---

## Bloco 1 — Demonstração prática

Roteiro de demonstração (ensaie cronometrando, alvo: 3 minutos):

**1. Cadastrar** → preencha *Nome: João Pereira / E-mail: joao@email.com / Curso: Técnico em Informática* → **Cadastrar**.
> "O formulário envia por POST para o `cadastrar.php`, que valida os campos, faz o INSERT com Prepared Statement e me devolve para a listagem com a mensagem de sucesso."

**2. Listar e filtrar** → digite `Sistemas` na busca → **Buscar** → depois **Limpar**.
> "A busca usa `WHERE ... LIKE ?` com o termo entre `%`, então encontra o texto em qualquer parte do nome, e-mail ou curso. Com a busca vazia aparecem todos."

**3. Editar** → clique em **Editar** no João → mude o curso → **Salvar alterações**.
> "O formulário já vem preenchido com os dados do banco. Ao salvar, o `atualizar.php` faz o UPDATE só daquele id."

**4. Excluir** → clique em **Excluir** no João → **Cancelar** (mostre que nada aconteceu) → **Excluir** de novo → **OK**.
> "Antes de enviar, o `confirm()` pergunta se tenho certeza. Se eu cancelo, o formulário não é enviado. Se confirmo, vai por POST para o `excluir.php`."

---

## Bloco 2 — Segurança

### 5. Como seu sistema se protege contra SQL Injection?
> "Uso Prepared Statements em todas as consultas, inclusive na listagem. Eu escrevo o SQL com `?` no lugar dos valores, o `prepare()` manda essa estrutura para o banco, e o `bind_param()` manda os dados separados. Por isso o banco nunca interpreta o que o usuário digitou como comando SQL."

**Mostrar:** `cadastrar.php` linhas 27–28.
**Demonstrar (impressiona):** digite `' OR '1'='1` na busca → aparece "Nenhum registro encontrado". Se o SQL fosse concatenado, isso listaria tudo.

### 6. O que aconteceria se alguém digitasse `<script>alert('hack')</script>` no nome?
> "O texto é salvo no banco do jeito que foi digitado, mas quando mostro na tela passo pelo `htmlspecialchars()`. Ele troca `<` por `&lt;`, `>` por `&gt;` e as aspas por entidades. O navegador então **mostra** o texto em vez de **executar** o script. Isso protege contra XSS."

**Mostrar:** `index.php` linhas 78–81.
**Demonstrar:** cadastre esse nome e mostre que aparece como texto, sem alerta.
**Detalhe:** "Uso `ENT_QUOTES` para converter também as aspas simples, e `'UTF-8'` para os acentos."

> ⚠️ Não confunda: `htmlspecialchars` protege a **saída** (XSS). Prepared Statement protege o **banco** (SQL Injection). A rubrica tira ponto de quem mistura os dois.

### 7. Por que você usou POST para exclusão em vez de GET?
> "GET é para buscar informação, não deve alterar dados. Se a exclusão fosse por link (`excluir.php?id=3`), ela poderia acontecer sem querer: alguém manda o link, o navegador pré-carrega a página, um robô de busca segue o link, ou a pessoa reabre pelo histórico. Com POST, só apaga quando o formulário é enviado de propósito. E no `excluir.php` eu recuso qualquer requisição que não seja POST."

**Mostrar:** `excluir.php` linhas 6–9.
**Demonstrar:** digite `localhost:8080/excluir.php?id=1` na barra de endereço → "Método não permitido para exclusão."

---

## Bloco 3 — Funcionalidades

### 8. Caminho completo desde o clique em "Editar" até a atualização no banco
Fale na ordem, apontando cada arquivo:
1. **`index.php` linha 84**: o botão Editar é um link para `editar.php?id=2`.
2. **`editar.php` linha 5**: pego o id da URL e valido que é um número inteiro.
3. **`editar.php` linhas 11–15**: `SELECT ... WHERE id = ?` busca o aluno.
4. **`editar.php` linhas 41–56**: mostro o formulário preenchido. O id vai num `input hidden`.
5. O usuário altera e clica em **Salvar**. O formulário faz POST para `atualizar.php`.
6. **`atualizar.php`**: valido os campos, verifico se o id existe (linhas 34–37) e faço o `UPDATE ... WHERE id = ?` (linhas 47–48).
7. Redireciono para o `index.php` com a mensagem de sucesso (linha 66).

### 9. Como você preenche o formulário de edição com os dados do banco?
> "No `editar.php` faço um SELECT pelo id e guardo a linha num array com `fetch_assoc()`. Depois coloco cada campo no atributo `value` do input, sempre passando pelo `htmlspecialchars`. Se o nome tiver aspas, por exemplo, isso impede que o HTML do input quebre."

**Mostrar:** `editar.php` linha 15 (`fetch_assoc`) e linha 48 (`value="..."`).

### 10. Como você confirma a exclusão antes de apagar?
> "O botão Excluir fica dentro de um formulário com `onsubmit="return confirm(...)"`. O `confirm()` mostra a caixa com OK e Cancelar. Se a pessoa clica em Cancelar, ele retorna `false`, e o `return false` cancela o envio do formulário, então nada chega ao servidor."

**Mostrar:** `index.php` linhas 88–91.

---

## Bloco 4 — Código e boas práticas

### 11. Mostre um Prepared Statement e explique os `?`
**Mostrar:** `atualizar.php` linhas 47–48:
```php
$stmt = $conn->prepare('UPDATE alunos SET nome = ?, email = ?, curso = ? WHERE id = ?');
$stmt->bind_param('sssi', $nome, $email, $curso, $id);
```
> "Cada `?` é um espaço reservado para um valor. No `bind_param`, a string `'sssi'` diz o tipo de cada um, na ordem: `s` é string e `i` é inteiro. Então nome, e-mail e curso são strings e o id é inteiro. Depois o `execute()` roda a consulta."

### 12. O que acontece se tentar atualizar um registro que não existe?
> "Antes do UPDATE, faço um SELECT para ver se o id existe. Se não existe, volto para a listagem com 'Registro não encontrado'. Faço isso porque o `affected_rows` sozinho não basta: ele dá 0 tanto quando o id não existe quanto quando a pessoa salva sem mudar nada. Com o SELECT antes, consigo separar os dois casos: um mostra 'Registro não encontrado' e o outro, 'Nenhuma alteração foi feita'."

**Mostrar:** `atualizar.php` linhas 34–44 e 54.
**Demonstrar:** acesse `localhost:8080/editar.php?id=9999` → "Registro não encontrado."

### 13. Onde você faz a conexão e como trata erros?
**Mostrar:** `conexao.php` inteiro (é curto).
> "Na linha 14 crio a conexão orientada a objetos com `new mysqli()`. Na linha 17 verifico o `connect_errno`: se for diferente de zero, deu erro. Aí registro o erro técnico no log e mostro uma mensagem genérica, para não expor detalhes do servidor. Na linha 11 uso o `mysqli_report(MYSQLI_REPORT_OFF)` porque, a partir do PHP 8.1, o mysqli lança uma exceção quando a conexão falha, e o programa pararia antes de chegar no meu `if`. Na linha 24 defino `utf8mb4` para os acentos."

---

## Bloco 5 — Perguntas específicas (prováveis)

O professor escolhe duas perguntas sobre o seu código. Estas são as mais prováveis:

**Por que `mysqli_report(MYSQLI_REPORT_OFF)`?** → ver pergunta 13.

**Por que o `%` fica na variável e não no SQL?** (`index.php` linha 15)
> "O `?` só pode representar um valor inteiro. Se eu escrevesse `LIKE '%?%'`, o `?` ficaria dentro de uma string e não seria substituído. Então monto `'%' . $busca . '%'` no PHP e passo o termo completo."

**O que o `trim()` faz?**
> "Remove os espaços do início e do fim. Sem ele, alguém poderia cadastrar um nome só com espaços e passar pela validação de campo vazio."

**Por que validar no PHP se o HTML já tem `required`?**
> "O `required` só funciona no navegador. Qualquer pessoa pode tirar isso pelo F12 ou mandar a requisição direto, por exemplo com o Postman. A validação no servidor é a que realmente garante."

**O que faz o `filter_input(..., FILTER_VALIDATE_INT)`?**
> "Garante que o id é um número inteiro. Se vier `abc` ou vazio, retorna `false`/`null`, e eu mostro 'Registro não encontrado'."

**O que faz o `header('Location: ...')` seguido de `exit`?**
> "O `header` redireciona o navegador para outra página, e o `exit` para o script ali, para nada mais ser executado depois. Redirecionar após o POST também evita que um F5 reenvie o formulário e cadastre duas vezes."

**O que é o `get_result()` e o `fetch_assoc()`?**
> "O `get_result()` pega o resultado do SELECT. O `fetch_assoc()` devolve uma linha por vez como array, com o nome da coluna como chave, por exemplo `$aluno['nome']`. No `while`, ele vai lendo até acabarem as linhas."

**Qual a diferença entre `num_rows` e `affected_rows`?**
> "`num_rows` é quantas linhas um SELECT **retornou**. `affected_rows` é quantas linhas um INSERT, UPDATE ou DELETE **alterou**."

**Para que serve o `label for`?**
> "Liga o texto ao campo pelo `id`. Clicar no texto foca o campo, e leitores de tela anunciam o nome certo, o que ajuda na acessibilidade. O `name` é o que vira a chave no `$_POST`."

---

## Bloco 6 — Bônus

### 16. O que é SQL Injection e como evitar?
> "É quando o atacante digita um pedaço de SQL num campo e, como o sistema monta a consulta juntando textos, esse pedaço vira parte do comando. Exemplo: `' OR '1'='1` numa tela de login faz a condição ser sempre verdadeira. Para evitar, uso Prepared Statements, com os dados separados do comando. Nunca concateno o que o usuário digitou dentro do SQL."

### 17. Diferença entre `mysqli_query()` e `mysqli_prepare()`?
> "O `mysqli_query()` executa um SQL pronto, de uma vez. Se o SQL tiver dados do usuário concatenados, fica vulnerável. O `mysqli_prepare()` prepara um SQL com `?`. Os valores entram depois pelo `bind_param` e o banco os trata sempre como dados. Por isso é seguro. Eu uso a versão orientada a objetos, `$conn->prepare()`, que é a mesma coisa."

### 18. Cite 3 boas práticas que você aplicou
Escolha 3 (tenha 5 prontas, caso ele peça mais):
1. Prepared Statements em **todas** as consultas.
2. `htmlspecialchars` com `ENT_QUOTES` e `UTF-8` em **todas** as saídas.
3. Exclusão só por POST, com `confirm()`.
4. Validação no servidor (`trim`, `filter_var`, `filter_input`) além do `required`.
5. Não mostrar erro técnico ao usuário: vai para o `error_log`, e o usuário vê uma mensagem amigável.

---

## Plano de estudo até a oral

| Dia | O que fazer |
|---|---|
| 28/09 | Ler os 6 arquivos com calma. Anotar qualquer linha que não souber explicar e tirar a dúvida. |
| 29/09 | Blocos 2 e 4 em voz alta, sem olhar. Refazer os que travar. |
| 30/09 | Blocos 3, 5 e 6 em voz alta. Ensaiar a demonstração do Bloco 1 cronometrando. |
| 01/10 | Simulado completo: alguém lê as perguntas em ordem aleatória e você responde mostrando no código. Rodar o checklist do dia. |
| 02/10 | Revisar só os ⚠️ e respirar. |
