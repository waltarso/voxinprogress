# VIP - Guia de Teste (Atualizado)

## Ambiente local

```bash
cd d:\Walter\vip\site\vip
php -S localhost:8000
```

Acesse: `http://localhost:8000`

> No `php -S`, `.htaccess` não é aplicado. As URLs ficam com `?p=...`.

---

## Checklist funcional

### Home
- [ ] Hero com fundo preto e imagem `pub.png`
- [ ] Botões: `Ver Músicas` e `Próximos Shows`
- [ ] Links sociais do VIP visíveis (Instagram/Facebook)
- [ ] Seção `Últimas Músicas` exibe somente itens com `homeOrder`
- [ ] Ordem da seção é crescente por `homeOrder`

### Músicas / Repertório
- [ ] Menu mostra `Músicas`
- [ ] Página mostra `Repertório de Músicas`
- [ ] Filtro por álbum funciona
- [ ] Busca por texto funciona
- [ ] URL amigável em produção: `/arranjos/<albumId>`
- [ ] Em local, fallback funciona via query string

### Detalhe da música
- [ ] Breadcrumb e metadados corretos
- [ ] Arquivos agrupados por tipo
- [ ] Botão `Voltar ao Repertório`

### Cantores (lista)
- [ ] Foto e nome clicam para o perfil
- [ ] Não existe mais botão `Ver Perfil Completo`
- [ ] Card mostra voz e bio curta

### Cantor (perfil)
- [ ] Conteúdo ocupa largura total (sem painel lateral)
- [ ] Foto carregada de `equipe/<id>/` quando disponível
- [ ] Bio completa carregada de `equipe/<id>/<id>.md` (ou `bio.md`)
- [ ] Links exibem redes sociais + WhatsApp + Email (se preenchidos no JSON)

### Contato
- [ ] CSRF e honeypot funcionando
- [ ] Validação de campos obrigatórios

---

## Checklist de dados

### `app/data/arranjos.json`
- [ ] Todos os itens necessários têm `homeOrder` numérico
- [ ] Itens sem `homeOrder` não aparecem na Home

### `app/data/cantores.json`
- [ ] `id` corresponde à pasta em `equipe/<id>/`
- [ ] `whatsapp` e `email` preenchidos quando houver
- [ ] `links` com Instagram/Facebook corretos

### `equipe/<id>/`
- [ ] Existe `<id>.md` para cada cantor
- [ ] Foto com nome padrão (`foto.*`) ou qualquer imagem válida

---

## Smoke tests rápidos

```bash
# Home
curl -I "http://localhost:8000/?p=home"

# Repertório
curl -I "http://localhost:8000/?p=arranjos"

# Perfil cantor
curl -I "http://localhost:8000/?p=cantor&id=walter_de_tarso"
```

---

## Observações

- Em produção Apache, confirme `mod_rewrite` e `AllowOverride All`.
- Se aparecer texto com caracteres estranhos (Ã, Â), revisar codificação dos arquivos para UTF-8 sem BOM.
