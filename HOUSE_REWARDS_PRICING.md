# House Rewards – dreistufige Preise auf den Karten

Auf der Startseite in der Section **House Rewards** kann unter dem Spieltitel eine Kette angezeigt werden:

**Rabatt-Badge (-X %)** bezieht sich auf House Rewards **immer auf den regulären UVP** (WooCommerce „Regulärer Preis“) **und den House-Mitgliederpreis** – nicht nur auf den WooCommerce-Angebotspreis. So wird der höhere Ersparnis für Mitglieder korrekt angezeigt (z. B. UVP 4,99 € → Mitglied 2,22 € ≈ **-56 %**, auch wenn der Shop-Rabatt nur -44 % wäre).

Dazu: **Originalpreis (durchgestrichen)**, **rabattierter Shop-Preis (durchgestrichen)**, **House-Mitgliederpreis (weiß, fett)** – nebeneinander mit Abstand, ohne Pfeile.

## Wo der Mitgliederpreis im Frontend greift

- **Anzeige (Listen, Startseite):** Der WooCommerce-Filter auf `get_price` und der Teaser „House-Mitglieder: …“ gelten **nur in der Section House Rewards** (Kontext-Stack beim Rendern jeder Karte). In **Featured, Bestseller, Shop-Archiv** usw. sehen Mitglieder den **normalen Shop-Preis** wie Gäste (ohne Teaser-Zeile).
- **Kauf:** Sobald ein Produkt **in den Warenkorb** gehört bzw. Totals berechnet werden, auf der **Produktseite**, **Warenkorb-** und **Kassen-Seite** sowie bei relevanten **Woo-AJAX**-Aktionen (`add_to_cart` / `remove_from_cart`) wird der **Mitgliederpreis** wieder angewendet, damit der Warenkorb stimmt.

Technisch: `globalkeys_house_rewards_pricing_context_enter/leave` in `template-parts/section-house-members.php`, Steuerung in `globalkeys_house_member_price_filters_applicable_context()` in `inc/gk-house-member-access.php`. Erweiterbar per Filter `globalkeys_house_member_price_filters_applicable_context`.

## Wo du das einstellst (WooCommerce Admin)

Alles läuft über die **Produktdaten** – es gibt keinen separaten „House Rewards“-Preisrechner.

### 1. Regulärer Preis („Original“)

- Produkt bearbeiten → Tab **Allgemein**
- Feld **Regulärer Preis** = erste Zahl in der Kette (UVP / Listenpreis)

### 2. Angebots-/Aktionspreis („normal rabattiert“)

- Feld **Angebotspreis** = zweite Zahl (was **alle Kunden** im Shop zahlen, wenn kein Mitgliedspreis greift)
- Ohne Angebotspreis: Es gibt nur **zwei** Stufen (Listenpreis durchgestrichen, Mitgliedspreis), kein mittlerer Schritt; kein Prozent-Badge ohne Sale.

### 3. House-Mitgliederpreis (dritte Zahl)

- Dasselbe Produkt → weiter unten bei den Preisfeldern: **House-Mitgliederpreis** (Theme-Feld)
- Muss **niedriger** sein als der aktuelle Verkaufspreis aus (2) (bzw. als regulärer Preis, wenn kein Angebot gesetzt ist)
- Nur sichtbar / wirksam für Nutzer mit House-/Abo-Zugang (wie im Theme beschrieben); **auf der Karte** wird die Kette für alle Besucher als Info angezeigt

### Variable Produkte

- Den **House-Mitgliederpreis** je **Variation** setzen (bei Variationen einzeln bearbeiten).
- Die Karte nutzt die **günstigste** Variation, bei der ein gültiger Mitgliedspreis hinterlegt ist, und zeigt deren drei Stufen.

## Section „House Rewards“ – welche Produkte?

- **Design → Customizer → House Members: Zugang → „House Rewards: Produkt-IDs“**  
  Kommagetrennt bis zu 6 WooCommerce-Produkt-IDs. Leer = Standard-Liste per Slug im Code (`inc/sections.php`, Konstante `GLOBALKEYS_HOUSE_REWARDS_MAX`, Funktion `globalkeys_get_house_members_curated_product_ids`, Filter `globalkeys_house_members_product_slug_groups`). In dieser Section werden **Vorbesteller nicht aus der Abfrage gefiltert**, damit alle 6 Slots belegt werden können. Fehlen nach Slugs oder Customizer weniger als 6 gültige Produkte, füllt **`globalkeys_house_rewards_fill_curated_ids_to_limit()`** die Liste mit Produkten vom Tag **house-members**, dann **Featured**, dann **Beliebtheit** (ohne Duplikate).

## Texte & Promo (rot unter dem Titel auf der Karte)

- **Customizer → House Members: Zugang**
  - Badge-Text „Premium Discount“
  - Text „Ends April“ (oder eigener Text)

## Anpassung per Code (fortgeschritten)

- Filter **`globalkeys_house_rewards_price_ladder_segments`** – Segmente (`strike` + `html`) vor dem Rendern ändern.
- Filter **`globalkeys_house_rewards_price_ladder_html`** – komplettes Markup ersetzen.

## Schnelltest mit Beispielwerten (WP-CLI)

Nach dem Einsetzen der echten Produkt-IDs (z. B. aus dem Customizer oder aus der Datenbank):

```bash
wp post meta update PRODUKT_ID _regular_price 4.99
wp post meta update PRODUKT_ID _sale_price 2.78
wp post meta update PRODUKT_ID _price 2.78
wp post meta update PRODUKT_ID _gk_house_member_price 2.22
```

`PRODUKT_ID` ersetzen. Bei **Steuern inkl.** ggf. dieselben Werte wie im Backend verwenden. Danach unter **WooCommerce → Status → Tools** ggf. **Produkte neu synchronisieren** ausführen.

---

*Theme: globalkeys – Logik in `inc/gk-house-member-access.php` (`globalkeys_get_house_rewards_price_ladder_*`), Darstellung in `template-parts/product-card-bestseller.php` + `style.css` (`.gk-house-rewards-price-ladder`).*
