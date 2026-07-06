<div class="plugin-help-content">

    {{-- Banner de Introdução --}}
    <header>
        <h3>
            <x-lucide-form-input class="lucid-icon" /> Construtor de Formulários Dinâmicos
        </h3>
        <p>
            Crie formulários personalizados, gerencie as respostas recebidas e configure notificações automáticas por e-mail, tudo de forma modular e sem mexer no código.
        </p>
    </header>

    {{-- Passo 1: Como Incorporar --}}
    <h3>
        1. Como Exibir o Formulário no Site
    </h3>
    <p>Você pode exibir qualquer formulário ativo de um jeito extremamente simples: via Shortcode</p>

    <p>Copie e cole a tag abaixo em qualquer campo de texto de <strong>Páginas</strong> ou <strong>Posts do Blog</strong>:</p>
    <div class="code">
        [form slug="seu-slug-aqui"]
    </div>

    {{-- Passo 2: O Construtor de Campos --}}
    <h3>
        2. Guia de Configuração de Campos
    </h3>
    <p>Ao adicionar campos ao formulário, atente-se a estes parâmetros fundamentais:</p>

    <ul>
        <li>
            <strong>Key (name):</strong> Deve ser única dentro do formulário, em letras minúsculas e sem espaços (ex: <code>nome_completo</code>, <code>telefone</code>). Ela serve como identificadora no banco de dados e nos e-mails de notificação.
        </li>
        <li>
            <strong>Tipo:</strong> Escolha entre Texto Curto, Texto Longo (Textarea), E-mail, Número, Dropdown (Select), Radio, Checkbox, Switch ou Oculto (Hidden).
        </li>
        <li>
            <strong>Prefixos e Sufixos:</strong> Elementos visuais para auxiliar o preenchimento (ex: colocar <code>R$</code> como prefixo em campos financeiros ou <code>,00</code> como sufixo).
        </li>
    </ul>

    {{-- Regra de Ouro: Opções --}}
    <blockquote>
        <strong>Como configurar Opções (Select, Radio e Checkbox):</strong>
        <p>
            Para campos de múltipla escolha, digite uma opção por linha no formato <code>valor|Rótulo Visível</code>.<br>
            <strong>Exemplo:</strong><br>
            <code>suporte|Dúvidas e Suporte Técnico</code><br>
            <code>financeiro|Financeiro e Cobrança</code>
        </p>
    </blockquote>

    {{-- Passo 3: Validação do Laravel --}}
    <h3>
        3. Validação Segura de Dados (Regras)
    </h3>
    <p>O campo <strong>Regras de Validação</strong> aceita os validadores nativos do Laravel. Você pode separar múltiplos validadores usando o caractere de barra vertical (<code>|</code>):</p>

    <table>
        <thead>
            <tr">
                <th>Regra</th>
                <th>O que faz</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>required</code></td>
                <td class="txt">Torna o preenchimento do campo obrigatório.</td>
            </tr>
            <tr>
                <td><code>email</code></td>
                <td class="txt">Garante que o valor digitado seja um endereço de e-mail válido.</td>
            </tr>
            <tr>
                <td><code>numeric</code></td>
                <td class="txt">Permite apenas valores numéricos.</td>
            </tr>
            <tr>
                <td><code>min:X | max:Y</code></td>
                <td class="txt">Define o tamanho mínimo ou máximo de caracteres/valores.</td>
            </tr>
        </tbody>
    </table>

    {{-- Passo 4: Notificações SMTP --}}
    <h3>
        4. Configuração de Notificações por E-mail
    </h3>
    <p>Ao preencher o campo "Enviar respostas para (E-mail)" no cabeçalho do formulário, o sistema disparará um e-mail estruturado contendo todos os dados do envio.</p>

    <blockquote>
        <strong>Requisito obrigatório para envio de e-mails:</strong>
        <p>
            Os e-mails só serão entregues se as configurações de servidor SMTP estiverem corretamente preenchidas no painel do administrador em <strong>Admin → Configurações → E-mail</strong>.
        </p>
    </blockquote>
</div>
