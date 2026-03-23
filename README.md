# GitPushPlay — File Structure Guide

This document explains where files live and where to put new ones.

---

## Project Root

```
Git-Push-Play/
├── app/                  ← All web-facing code lives here
├── introductions/        ← Team intro text files (you wont need to touch this anymore)
└── README.md
```

The `app/` folder is the websites root. Everything outside of it is not publicly accessible (in practice).

---

## app/ Layout

```
app/
├── index.php             ← Homepage
├── login.php             ← Login page
├── create-account.php    ← Registration page
├── demos.php             ← Demo Vault page
│
├── includes/             ← Reusable PHP partials (not pages)
│   ├── _meta.php         ← <head> meta/title tags
│   ├── _header.php       ← Site header and nav bar
│   ├── _footer.php       ← Site footer
│   └── _login.php        ← Auth guard — redirects to login if not logged in
│
├── assets/               ← Static files (CSS, JS, images, libraries)
│   ├── css/
│   │   └── style.css     ← Global stylesheet (colors, typography, layout)
│   ├── js/
│   │   └── main.js       ← Global JavaScript
│   ├── images/
│   │   └── favicon.ico
│   └── libs/
│       ├── auth.php      ← User auth functions (login, create account, etc.)
│       └── phpfunctions.php ← General PHP helper functions
│
└── secure/
    └── users.json        ← User account data will go here
```

---

## How to Create a New Page

1. **Create your file** in `app/` — e.g. `app/schedule.php`

2. **Copy this or add it in piece by piece:**

```php
<?php include 'includes/_login.php'; ?>  <!-- remove if page is public -->
<?php
$title       = "Page Title";
$description = "Short description of this page.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/_meta.php'; ?>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <?php include 'includes/_header.php'; ?>

    <main>
        <!-- Your page content here -->
    </main>

    <?php include 'includes/_footer.php'; ?>

</body>
</html>
```

3. **Add a link** in `includes/_header.php` so it appears in the nav.

---

## Where Things Go

| What you're adding         | Where it goes                        |
|----------------------------|--------------------------------------|
| New page                   | `app/yourpage.php`                   |
| Page-specific CSS          | Inline `<style>` in your page file   |
| Shared/global CSS          | `app/assets/css/style.css`           |
| JavaScript                 | `app/assets/js/main.js` or inline    |
| Images / icons             | `app/assets/images/`                 |
| PHP helper functions       | `app/assets/libs/phpfunctions.php`   |
| Reusable HTML partials     | `app/includes/_yourpartial.php`      |

---

## Key Rules

- **`_login.php`** — include this at the very top of any page that requires login. It redirects unauthenticated users automatically.
- **`_meta.php`** — set `$title` and `$description` variables *before* including it to customize each page's title and meta description.
- **`secure/`** — the `users.json` file stores hashed passwords. Do not link to or expose anything in this folder.
- **`assets/libs/`** — PHP-only utility files. Include them with `include 'assets/libs/auth.php'` etc. Do not put HTML here.
- **Style variables** — defined in `style.css` under `:root {}`. Use them in your page CSS rather than hardcoding colors, so the site stays visually consistent.
