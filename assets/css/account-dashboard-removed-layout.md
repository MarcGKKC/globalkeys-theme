# Account Dashboard – Entferntes Layout (für später)

Diese Boxen wurden temporär entfernt. Layout zur Wiederherstellung:

## 1. TABS

```html
<nav class="gk-accdash__tabs" aria-label="Account Navigation">
  <!-- Links: Dashboard | Orders | Downloads | Log out (WooCommerce menu_items) -->
</nav>
```

- **CSS:** `.gk-accdash__tabs` – height: 69px, border-radius: 6px, padding: 0 22px, margin-bottom: 40px
- Horizontale Tab-Navigation unter dem Hero
- Filter: edit-account, edit-address, payment-methods aus menu_items überspringen

## 2. MAIN GRID (2 Spalten)

```html
<div class="gk-accdash__mainGrid">
  <aside class="gk-accdash__leftBox">
    <div class="gk-accdash__sideList">
      <!-- Sidebar-Items: Customize profile, Communications, Privacy, Language/currency, Email+password, Billing, Log out -->
    </div>
  </aside>
  <div class="gk-accdash__rightBig">
    <div class="woocommerce-MyAccount-content gk-accdash__content">
      <?php do_action( 'woocommerce_account_content' ); ?>
    </div>
  </div>
</div>
```

- **CSS:** grid-template-columns: 380px 1fr, gap: 37px
- **Links (380px):** Sidebar mit Icons + Titel + Beschreibung pro Item
- **Rechts:** WooCommerce Content (Dashboard, Orders, etc.)

## 3. Sidebar-Items (Beispielstruktur)

Jedes Item:
- Icon (SVG)
- sideTitle (z.B. "Customize your profile")
- sideDesc (z.B. "Avatar, gamertag and social links")
- Links: edit-account, edit-address, customer-logout

## 4. Vollständiger PHP-Block (my-account.php)

Siehe Git-History oder frühere Version für den kompletten HTML-PHP-Code der Tabs + Main Grid inkl. Sidebar-Links.
