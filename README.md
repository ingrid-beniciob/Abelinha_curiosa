# Abelinha Curiosa - E-commerce de Brinquedos Educativos

Bem-vindo ao projeto **Abelinha Curiosa**, um e-commerce dedicado à venda de brinquedos educativos e pedagógicos para crianças de todas as idades.

## 📋 Sobre o Projeto

Este projeto é uma loja virtual completa desenvolvida com **HTML, CSS, JavaScript** no frontend e **PHP** com banco de dados **MySQL** no backend. O sistema permite:

-   Navegação por categorias de produtos (Piticos, Fofinhos, Crescidinhos, Grandinhos).
-   Busca de produtos em tempo real.
-   Adição de produtos ao carrinho de compras.
-   Finalização de pedidos (Checkout).
-   Painel Administrativo para gerenciamento de produtos e pedidos.

## 🚀 Pré-requisitos

Para rodar este projeto localmente, você precisará ter instalado:

-   **XAMPP** (ou qualquer outro servidor web com Apache e MySQL/MariaDB).
    -   [Download do XAMPP](https://www.apachefriends.org/pt_br/download.html)

## 🔧 Instalação e Configuração

Siga os passos abaixo para configurar o projeto no seu computador:

### 1. Clonar ou Baixar o Projeto

Baixe os arquivos do projeto e mova a pasta `Abelinha_curiosa` para dentro do diretório `htdocs` do seu XAMPP.

-   **Caminho padrão no Windows:** `C:\xampp\htdocs\Abelinha_curiosa`

### 2. Iniciar o Servidor

1.  Abra o **XAMPP Control Panel**.
2.  Inicie os módulos **Apache** e **MySQL** clicando no botão "Start".

### 3. Configurar o Banco de Dados

1.  Acesse o **phpMyAdmin** no seu navegador: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2.  Crie um novo banco de dados com o nome: `abelinha_curiosa`
    -   **Charset:** `utf8mb4_unicode_ci` (recomendado)
3.  Selecione o banco de dados criado.
4.  Vá na aba **Importar**.
5.  Escolha o arquivo SQL localizado na pasta do projeto: `database/abelinha_curiosa.sql`
6.  Clique em **Executar** para importar as tabelas e dados iniciais.

> **Nota:** O arquivo de configuração do banco de dados está em `config/database.php`. Se você alterou a senha do root no XAMPP, precisará atualizar este arquivo.

## 🖥️ Como Usar

### Acessando a Loja

Abra seu navegador e acesse o seguinte endereço:

[http://localhost/Abelinha_curiosa](http://localhost/Abelinha_curiosa)

Você verá a página inicial da loja e poderá navegar pelas categorias e produtos.

### Acessando o Painel Administrativo

Para gerenciar produtos e visualizar pedidos, acesse a área administrativa:

[http://localhost/Abelinha_curiosa/pages/admin-login.html](http://localhost/Abelinha_curiosa/pages/admin-login.html)

**Credenciais de Acesso (Padrão):**
-   **Email:** `admin@abelinha.com`
-   **Senha:** `admin123`

## 📂 Estrutura do Projeto

-   `assets/`: Imagens e recursos visuais.
-   `config/`: Arquivos de configuração (banco de dados).
-   `css/`: Folhas de estilo (style.css).
-   `database/`: Arquivo SQL para importação do banco.
-   `js/`: Scripts JavaScript (carrinho, produtos, componentes).
-   `pages/`: Páginas HTML do site (categorias, carrinho, admin).
-   `php/`: Scripts PHP para processamento (API, login, listagem).
-   `index.html`: Página inicial.

---

## Próximas etapas

-   Implementar sistema de pagamento.
-   Implementar responsividade.
-   Implementar sistema de login para clientes.


**Desenvolvido com carinho por Ingrid Benicio - Criadora da Abelinha Curiosa 🐝**
