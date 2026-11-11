# Giacomo Licata – WordPress + Divi 5 version

This folder rebuilds the live React site (www.giacomolicata.co.uk) on WordPress with Divi 5. It has the same pages, URLs, text, images, layout, header, footer, sliders and News lightbox.

```
wordpress/
├── dist/giacomo-licata-divi-child.zip   ← upload this to WordPress
└── giacomo-licata-divi-child/           ← source of the child theme
    ├── style.css / functions.php
    ├── header.php / footer.php          ← fallback header/footer (used only if no Theme Builder ones)
    ├── inc/content.php                  ← the 7 pages + Theme Builder header/footer, as Divi 5 layouts
    ├── inc/installer.php                ← one-click setup (+ `wp gl install`)
    └── assets/                          ← CSS, JS, slick slider, all site images
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

- **Page content:** open any page with the Divi Builder. Each box (Opening Times, Prices, Services and so on) is a Text module, and the layer names match the box titles. Edit the text in place.
- **Sliders:** each one is a Text module. Every image inside it becomes a slide, so add or remove images to change the slides.
- **News and Products grids:** these are Text modules too. Copy an existing item in the HTML tab to add a new one.
- **Videos and map:** these are Code modules (YouTube and Google Maps iframes).
- **Header and footer:** Divi → Theme Builder → Default Website Template → edit "Giacomo Licata Header" or "Giacomo Licata Footer" in the Divi Builder. The logo, background credit and social icons are Text modules; change the image, text or links there.
- **Menu:** Appearance → Menus → "Giacomo Licata Menu". The header and footer show it through a Code module containing `[gl_nav type="header"]` / `[gl_nav type="footer"]`, which also highlights the current page. Leave those shortcodes in place.
- **Styling:** `assets/css/site.css`. Rows and columns use classes such as `gl-row--top`, `gl-col--slider`, `gl-col--box` and `gl-col--stack`, set under each element's Advanced → CSS ID & Classes.

The header has the classes `gl-header gl-tb-header`, which make it fixed and transparent and turn it #242424 after scrolling 50px. Its row is `gl-header__bar`. In the footer, the bar row is `gl-footer` and its columns are `gl-footer__section`. Keep those classes (Advanced → CSS ID & Classes) or the styling won't apply.

The theme also has a coded header and footer (`header.php` / `footer.php`). They're used only when no Theme Builder header or footer is assigned, for example if you untick the Theme Builder box or disable them in the Theme Builder. In that case the header logo comes from Divi → Theme Options → Logo, or the original logo if none is set.

## How faithful is it?

The pages were checked against the live site in a headless browser at 1440px and 390px wide:

- Every page has exactly the same height as the live site.
- After all images load, Home, About, Prices, Profiles and News are pixel-identical. The only differences left came from the live server failing to send an image, or from Google Maps/YouTube loading.

Some quirks of the live site are kept on purpose: stretched product photos, the 3-column News grid on mobile, the Prices box being slightly wider than Services, and typos such as "Aftershare". Two things differ from the code in `src/`:

- **Five team members, not six.** The live site shows Giacomo, Carmelo, Domenico, Alec and Gavin. Sean is in the repo source but was never deployed. To add him, put another `gl-team__item` in the Team module, using `sean-sm.jpg` from `src/assets/images/`.
- **The live header logo** (`giac-logo.png`, which has the flag) is used, not the older `logo.png`.

## Notes on the Divi 5 format

Pages are stored in Divi 5's native block format: `divi/placeholder` › `divi/section` › `divi/row` › `divi/column` › `divi/text` / `divi/code`. The JSON is written by WordPress core's own block serializer. Each block is stamped with the Divi version that's installed.

The header and footer are stored the way the Divi Theme Builder stores them: `et_header_layout` / `et_footer_layout` posts, assigned to the default `et_template` of the live `et_theme_builder` post.

Elegant Themes hasn't published a full specification of Divi 5's block attributes or Theme Builder storage. The attribute paths used here come from community documentation (see `inc/content.php`), and the content was only verified against a stand-in that renders the same markup Divi does. It has not been tested on a real Divi 5 install. If the header or footer doesn't appear after setup, open Divi → Theme Builder, check that the Default Website Template has them assigned, and click Save. If they still don't show, untick the Theme Builder box and re-run setup to use the coded fallback.

If the visual builder ever shows a module as empty or unrecognised, open and re-save the page in the Divi Builder. If that doesn't fix it, rebuild that one module and paste in the HTML from `inc/content.php`. The front end doesn't depend on Divi's design settings, because all styling comes from the child theme's CSS.
