# VIP - Guia de Teste

> ⚠️ **Nota**: após a instalação inicial, remova `install.php` e `status.php` do servidor.


## Como Testar o Site Localmente

### Opção 1: PHP Built-in Server (Recomendado)

```bash
# Navegue até a pasta do site
cd d:\Walter\site\vip

# Inicie o servidor (certifique-se que PHP está no PATH)
php -S localhost:8000

# Acesse no navegador
http://localhost:8000
```

**Se PHP não está no PATH:**

Encontre a pasta de instalação PHP (ex: `C:\Program Files\PHP`) e:

```bash
cd d:\Walter\site\vip
"C:\Program Files\PHP\php.exe" -S localhost:8000
```

### Opção 2: XAMPP/WAMP/LAMP

1. **XAMPP (Windows/Mac/Linux):**
   - Copie a pasta `vip` para `C:\xampp\htdocs\vip`
   - Abra http://localhost/vip

2. **WAMP (Windows):**
   - Copie para `C:\wamp\www\vip`
   - Abra http://localhost/vip

3. **LAMP (Linux):**
   - Copie para `/var/www/html/vip`
   - Abra http://localhost/vip

### Opção 3: Usar Docker

```dockerfile
# Dockerfile
FROM php:8.2-apache

WORKDIR /var/www/html

COPY vip /var/www/html

RUN a2enmod rewrite

EXPOSE 80
CMD ["apache2ctl", "-D", "FOREGROUND"]
```

```bash
docker build -t vip-site .
docker run -p 80:80 vip-site
```

---

## Checklist de Testes Manuais

### ✅ Home Page
- [ ] Logo/título aparece
- [ ] Hero section visível
- [ ] Botões "Ver Arranjos" e "Próximos Shows" funcionam
- [ ] Cards dos últimos arranjos aparecem
- [ ] Cards dos cantores aparecem
- [ ] Rodapé com copyright e email

### ✅ Arranjos (Catálogo)
- [ ] Lista de arranjos aparece
- [ ] Thumbnails das capas aparecem (retiradas de acervo se especificado)
- [ ] Dropdown de álbum funciona
- [ ] Busca por texto funciona
- [ ] Filtrar por álbum + busca simultaneamente funciona
- [ ] Botão "Limpar" volta ao estado inicial
- [ ] Cards com informações corretas

### ✅ Arranjo (Detalhe)
- [ ] Metadados aparecem (artista, álbum, ano, duração, dificuldade)
- [ ] Capa/imagem do arranjo aparece (se houver) e carrega de acervo
- [ ] Estrelas de dificuldade aparecem
- [ ] Arquivos agrupados por tipo
- [ ] Links de download funcionam (apontam para /vip/acervo corretamente)
- [ ] Breadcrumb funciona
- [ ] Botão "Voltar ao Catálogo".funciona

### ✅ Cantores
- [ ] Grid de cantores aparece
- [ ] Cada card tem nome, voz, bio
- [ ] Botão "Ver Perfil" funciona

### ✅ Cantor (Detalhe)
- [ ] Foto/avatar aparece
- [ ] Bio completa vísível
- [ ] Links externos funcionam (se houver)
- [ ] Breadcrumb e botões de navegação funcionam

### ✅ História
- [ ] Conteúdo de `historia.html` aparece
- [ ] Formatação HTML mantida (h2, h3, ulhref)
- [ ] Links laterais funcionam

### ✅ Agenda
- [ ] Próximos eventos listados
- [ ] Eventos passados separados e opacificados
- [ ] Datas formatadas em DD/MM/YYYY
- [ ] Hora mostra se houver
- [ ] Local exibido
- [ ] Descrição aparece

### ✅ Contato
- [ ] Formulário aparece
- [ ] Validação JavaScript (lado cliente)
- [ ] Campo obrigatório: nome
- [ ] Campo obrigatório: email
- [ ] Validação de email funciona
- [ ] Campo obrigatório: assunto
- [ ] Campo obrigatório: mensagem
- [ ] Telefone é opcional
- [ ] Ao submeter, mensagem de sucesso aparece
- [ ] CSRF protection funciona (alterar token quebra form)
- [ ] Honeypot oculto não interfere

### ✅ Navegação
- [ ] Menu navbar funciona em mobile (hamburger)
- [ ] Links ativos marcados na nav
- [ ] Home link leva para home
- [ ] Todos os links de nav acessíveis

### ✅ Design/Responsividade
- [ ] Desktop (1920x1080): layout correto
- [ ] Tablet (768x1024): layout responsivo
- [ ] Mobile (375x667): menu funciona, cards empilham
- [ ] Cores aparecem corretamente
- [ ] Botões visíveis e clicáveis
- [ ] Leitura confortável em todos os tamanhos

### ✅ Performance
- [ ] Página carrega rápida (< 2seg)
- [ ] Bootstrap CSS via CDN funciona
- [ ] Icons (Bootstrap Icons) aparecem
- [ ] Custom CSS carrega

### ✅ Segurança
- [ ] HTML escape: `<script>alert(1)</script>` em busca não executa
- [ ] Path traversal: `?p=../../../etc/passwd` dá 404
- [ ] ID validation: `?id=alert(1)` dá 404
- [ ] CSRF: tentar contato sem token falha
- [ ] Email: `x@y.z` inválido é rejeitado

---

## Testes Automatizados (Script)

```bash
#!/bin/bash
# test.sh - Script para testar URLs

BASE_URL="http://localhost:8000"

echo "Testando VIP..."

# Testar HTTP status codes
test_url() {
    url="$1"
    status=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL$url")
    echo "GET $url - $status"
}

# Home
test_url "/?p=home"
test_url "/index.php?p=home"

# Arranjos
test_url "/?p=arranjos"
test_url "/?p=arranjos&album=insanity"
test_url "/?p=arranjos&q=balada"

# Arranjo válido
test_url "/?p=arranjo&id=balada_do_louco"

# 404 esperado
test_url "/?p=arranjo&id=inexistente"

# Segurança
test_url "/?p=../../../etc/passwd"

echo "Testes concluídos!"
```

---

## Testes Via Navegador (DevTools)

### Console
```javascript
// Verificar se session csrf_token existe
fetch('http://localhost:8000/?p=contato').then(r => r.text());

// Testar API (se houver)
fetch('http://localhost:8000/api/arranjos').then(r => r.json());
```

### Network
- [ ] Verificar que `config.php` não é acessível
- [ ] Verificar que `app/data/*.json` não é acessível (se possível)
- [ ] Todos os assets carregam com 200 OK

### Performance
- [ ] Time to Interactive < 2 segundos
- [ ] First Contentful Paint < 1 segundo
- [ ] CLS (Cumulative Layout Shift) baixo

---

## Casos de Edge

### Testes Especiais

1. **Empty JSON**
   ```bash
   # Editar arranjos.json para []
   # Verificar se home, arranjos mostram "sem arranjos"
   ```

2. **Missing JSON Files**
   ```bash
   # Remover arranjos.json temporary
   # Verificar se site não quebra (deve mostrar vazio)
   ```

3. **Invalid JSON**
   ```bash
   # Editar arranjos.json: { invalid json
   # Verificar se site não quebra
   ```

4. **FormulárioContato Vazio**
   - Submeter form vazio
   - Submeter apenas nome
   - Submeter email inválido

5. **URL Malformada**
   - `?p=`
   - Muito longa
   - Com caracteres especiais

---

## Ferramenta Útil: install.php

```bash
curl http://localhost:8000/install.php
```

Mostra:
- ✅ Arquivos necessários
- PHP version
- Permissões de pasta
- JSONs carregados

---

## Teste de Carga (se necessário)

```bash
# Usar Apache Bench
ab -n 1000 -c 10 http://localhost:8000/?p=home

# Usar wrk (mais moderno)
wrk -t4 -c100 -d30s http://localhost:8000/?p=arranjos
```

---

## SEO Teste

Verificar:
- [ ] `<title>` está presente e único por página
- [ ] Meta description (adicionar em header.php se precisar)
- [ ] Heading structure (H1, H2, H3) correto
- [ ] Images têm alt text
- [ ] URLs amigáveis (se usando .htaccess)
- [ ] Schema.org markup (opcional)

---

## Validadores Online

- **HTML**: https://validator.w3.org/
- **CSS**: https://jigsaw.w3.org/css-validator/
- **Links**: https://validator.w3.org/checklink
- **Accessibility**: https://www.wave.webaim.org/

---

## Teste Final - Checklist de Deployment

- [ ] Todos os testes acima passam
- [ ] Nenhum erro no navegador (F12 > Console)
- [ ] Nenhum aviso de segurança
- [ ] Email configurado (se ENABLE_EMAIL = true)
- [ ] Backup dos JSON feito
- [ ] install.php e status.php removidos ou protegidos
- [ ] .htaccess configurado (se usando URLs amigáveis)
- [ ] Pronto para upload ao servidor!

---

Bom teste! 🎵✨
