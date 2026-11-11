# Giacomo Licata – WordPress + Divi 5 version

This folder rebuilds the live React site (www.giacomolicata.co.uk) on WordPress with Divi 5. It has the same pages, URLs, text and images. Every page, plus the header and footer, is built from **native Divi 5 modules** with the design set in Divi's own settings, so everything can be edited in the Divi Builder.

```
wordpress/
├── dist/giacomo-licata-divi-child.zip   ← upload this to WordPress
└── giacomo-licata-divi-child/           ← source of the child theme
    ├── style.css / functions.php
    ├── header.php / footer.php          ← fallback header/footer (used only if no Theme Builder ones)
    ├── inc/content.php                  ← the 7 pages + Theme Builder header/footer, as native Divi 5 layouts
    ├── inc/installer.php                ← one-click setup (+ `wp gl install`)
    └── assets/                          ← small CSS/JS extras, all site images
```

## Requirements

- WordPress 6.5 or newer, PHP 7.4 or newer
- The **Divi 5** parent theme, installed (it doesn't need to be active)

## Install

1. **Appearance → Themes → Add New → Upload Theme**, then choose `dist/giacomo-licata-divi-child.zip`.
   - The zip is about 31 MB because it includes every original image at full quality. If the upload limit on your host is lower, upload the `giacomo-licata-divi-child` folder to `wp-content/themes/` over SFTP instead.
2. Activate **Giacomo Licata (Divi 5 Child)**.
3. Go to **Appearance → Giacomo Licata Setup** and click **Install Giacomo Licata website**. The installer:
   - imports every image into the Media Library
   - creates **Home, About, Prices, Products, Location, Profiles, News** as Divi 5 builder pages
   - sets Home as the front page and turns on `/%postname%/` permalinks, so `/about`, `/prices` and the other old URLs keep working
   - creates the "Giacomo Licata Menu" and assigns it to the header and footer
   - creates a **Global Header** and **Global Footer** in **Divi → Theme Builder** (the "Create the header and footer in the Divi Theme Builder" box, ticked by default)
4. Optional: set **Settings → General → Site Title** to `Giacomo Licata`. The original site uses that as the browser tab title on every page.

You can also run it from WP-CLI with `wp gl install`. Add `--overwrite` to reset the pages and header/footer to the original content, or `--skip-theme-builder` to leave the Theme Builder alone.

The installer is safe to run again. It won't import images twice. It leaves alone pages you've already edited, pages it didn't create, and any header or footer already assigned in the Theme Builder, unless you tick **Overwrite**.

## Editing

Everything is edited in the Divi Builder, as on any Divi site:

| On the page | Divi module(s) | Where |
|---|---|---|
| Boxes (Opening Times, Prices, Services…) | **Group** (background, border, shadow) holding an **Image** (some boxes), a **Heading** and a **Text** | each page |
| Sliders | **Slider** with one **Slide** per image (the image is the slide background) | About, Prices, Products, Profiles |
| Home hero | **Section** background image + five **Heading** modules | Home |
| Products | Rows of 3 columns, each a **Group** of **Image + Heading + Text** (price) | Products |
| Team / News / Collection grids | Rows of 3 columns, each a **Group** of **Image + Text** (the hover caption) | Profiles, News |
| Videos | **Video** module (YouTube link) | About, News |
| Map | **Code** module with the Google Maps embed (Divi's own Map module needs a Google API key) | Location |
| Header / footer | **Image** logo + **Link** module looping over "Giacomo Licata Menu"; footer credit and social icons are **Text** | Divi → Theme Builder → Default Website Template |

- **Menu:** Appearance → Menus → "Giacomo Licata Menu". The header and footer Link modules repeat once per menu item.
- **News lightbox:** each News/Collection card's hover caption contains a link to the large image (Text module → link). Change that link to change what opens.
- **Adding a product or team member:** duplicate an existing column's Group in the Layers panel and change the image and text.

### The small custom extras

Divi has no setting for a few things on the original site, so the theme adds them (`assets/css/site.css`, `assets/js/site.js`). Keep these CSS classes (Advanced → CSS ID & Classes) on the elements that have them:

| Class | Where | What it does |
|---|---|---|
| `gl-header` | header Section | fixed over the page, transparent, turns #242424 after scrolling 50px |
| `gl-nav-item` | header menu Link module | current page shown in brown |
| `gl-hover-card` / `gl-hover-overlay` | Team/News Group / its caption Text | dark tint + caption on hover; News cards open the lightbox |
| `gl-footer-bar` | footer bar Row | 90% opacity, as on the original |

The theme also has a coded header and footer (`header.php` / `footer.php`). They're used only when no Theme Builder header or footer is assigned, for example if you untick the Theme Builder box or disable them in the Theme Builder.

## How faithful is it?

The layout, text, colours and spacing follow the original. These parts deliberately differ because they now use Divi's own modules:
- **Sliders** use Divi's arrows and dots, and the slides have a fixed height (412px on desktop) instead of following each image's shape.
- **Videos** use Divi's Video module, so the player is responsive (16:9) rather than a fixed 315px tall.
- **News/Collection images** keep their natural heights instead of being stretched to the tallest image in the row.
- **The Prices and Services boxes** are equal width (on the original, Prices was slightly wider).

Two things also differ from the code in `src/`:

- **Five team members, not six.** The live site shows Giacomo, Carmelo, Domenico, Alec and Gavin. Sean is in the repo source but was never deployed. To add him, duplicate a team card and use `sean-sm.jpg` from `src/assets/images/`.
- **The live header logo** (`giac-logo.png`, which has the flag) is used, not the older `logo.png`.

## Notes on the Divi 5 format

Pages are stored in Divi 5's native block format (`divi/placeholder` › `divi/section` › `divi/row` › `divi/column` › modules). Module settings follow the community [divi5-skill](https://github.com/divilovewp/divi5-skill) reference, which documents Divi 5.11–5.16. Every page and layout passes that reference's validation checklist. The JSON is written by WordPress core's block serializer, and each block is stamped with the installed Divi version.

The header and footer are stored the way the Divi Theme Builder stores them: `et_header_layout` / `et_footer_layout` posts, assigned to the default `et_template` of the live `et_theme_builder` post. Divi's own helper functions are used to create them where available.

**Not yet tested on a real Divi 5 site.** This build needs checking on a test site before going live. In particular, check that the box images are cropped to 200px, the boxes are equal height, and the header menu shows all seven links.

If something doesn't look right:
- Run setup again with **Overwrite** ticked.
- If a slider shows its slides stacked, Divi's module cache is stale. Setup clears it, but re-saving the page in the Divi Builder also fixes it.
- If the header or footer doesn't appear, open Divi → Theme Builder, check that the Default Website Template has them assigned, and click Save.
