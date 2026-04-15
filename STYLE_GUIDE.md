# Style guide — Creative portfolio (WordPress / Elementor target)

This document mirrors the **creative-operator** React portfolio (`/Users/xander/code/creative-operator`). Use it as the build reference so colors, type, and section order stay consistent throughout implementation.

**Related files**

| Purpose | Path |
|--------|------|
| Source app (visual + component reference) | `/Users/xander/code/creative-operator` |
| Copy, media URLs, anchors, Elementor hints | `/Users/xander/code/creative-operator/wordpress/portfolio-content-manifest.json` |
| This style guide | `/Applications/XAMPP/xamppfiles/htdocs/wordpress/STYLE_GUIDE.md` |

---

## Design intent

- **Look:** Dark “Catppuccin Mocha” base with **mauve** and **Tokyo Night–style blue** accents; soft purple/blue hero wash (slow drift in code; optional subtle motion in Elementor).
- **Feel:** Technical, editorial, broadcast/production — monospace typography, uppercase tracked kickers, generous section padding.
- **Layout:** Single long homepage with **anchor navigation**; max content width ~**1280px** with comfortable horizontal padding.

---

## Typography

| Role | In code | Elementor / WP suggestion |
|------|---------|---------------------------|
| All UI text | **JetBrains Mono** (Google Fonts) | Set as global body + headings, or match in Elementor Site Settings |
| Kickers | `text-xs`, uppercase, `tracking-[0.3em]` (wide letter-spacing) | Small label, uppercase, letter-spacing ~0.2–0.3em |
| H1 (hero name) | Large display, tight line-height ~0.9 | Heading widget, tight line height |
| H2 sections | `text-3xl` → `lg:text-5xl` scale | Responsive heading sizes |
| Body / muted | Muted color token below | Text color ~ `#9399b2`–ish (subtext) |

**Google Fonts import (reference):** JetBrains Mono weights 300–700 ([same as source](https://fonts.google.com/specimen/JetBrains+Mono)).

---

## Color system

Values below are **HSL components** as used in CSS (`hsl(H S% L%)`). **Approximate hex** is given for Elementor global color pickers (rounding).

### Core surfaces & text

| Token | HSL | Approx. hex | Usage |
|-------|-----|-------------|--------|
| Background | `240 21% 15%` | `#1e1e2e` | Page background (Catppuccin Mocha base) |
| Foreground | `227 64% 88%` | `#cdd6f4` | Default body text |
| Text bright | `226 64% 88%` | `#cdd6f4` | Headings emphasis |
| Muted foreground | `228 17% 55%` | `#7f849c` | Secondary copy, captions |
| Border | `240 13% 26%` | `#45475a` | Cards, dividers (close to Surface1) |

### Surfaces (cards / bands)

| Token | HSL | Approx. hex | Usage |
|-------|-----|-------------|--------|
| Surface 0 | `240 21% 20%` | `#313244` | Elevated panels, alt sections |
| Surface 1 | `240 17% 26%` | `#45475a` | Inner video/image areas |
| Surface 2 | `233 12% 39%` | `#585b70` | Scrollbar, subtle UI |

### Brand accents (use consistently per section)

| Token | HSL | Approx. hex | Usage in source |
|-------|-----|-------------|------------------|
| Mauve (primary) | `267 84% 81%` | `#cba6f7` | Hero kicker, primary CTA, stats, gradients, selection |
| Lavender | `232 97% 85%` | `#b4befe` | Gradients with mauve |
| Tokyo blue | `220 65% 65%` | ~`#5b9bd4` | Nav hover, secondary accent, “Creative × Technical” header kicker, contact gradient |
| Tokyo cyan | `187 40% 55%` | ~`#5e9ca0` | Gradient pairs with tokyo-blue |
| Peach | `23 70% 70%` | ~`#e8a077` | Campaign section kicker, pillar accent |
| Teal | `170 45% 65%` | ~`#6fc5b4` | Live section kicker, live indicator |
| Rosewater | `10 45% 85%` | ~`#f5e0dc` | Timeline accent (Moringa node) |

### Gradients (hero name & highlights)

1. **Hero surname gradient** (`text-gradient-hero-name`): 120deg — mauve → lavender → tokyo-blue.
2. **“Career timecode”** title span (`text-gradient-mauve`): 135deg — mauve → lavender.
3. **Contact headline accent** (`text-gradient-tokyo`): 135deg — tokyo-blue → tokyo-cyan.

**Hero background (approximation):** Dark base wash + soft **mauve** radial glow top-left (~22% opacity) + soft **tokyo-blue** radial bottom-right (~14% opacity), plus bottom **fade to background**gradients. In Elementor: section background gradient + overlay images or multiple gradient layers.

### UI tokens

| Token | HSL | Notes |
|-------|-----|--------|
| Primary (button fill) | `267 84% 81%` | Mauve |
| Primary foreground | `240 21% 12%` | Dark text on mauve buttons |
| Ring / focus | `267 84% 81%` | Mauve |
| Radius | `0.75rem` (**12px**) | Cards, buttons |

---

## Layout & spacing

| Pattern | In code | Elementor hint |
|---------|---------|----------------|
| Max width | `max-w-7xl` → **1280px** | Section content width ~1180–1280px |
| Horizontal padding | `px-4 sm:px-6 lg:px-12` | Responsive section padding |
| Section vertical rhythm | `py-14 lg:py-20` | ~56px / 80px vertical |
| Hero min height | ~`58vh`–`70vh` | Full-width section, min height |
| Fixed nav offset | `scroll-padding-top: 4rem + safe-area` | Menu anchors: set **CSS ID** on sections; add top offset if needed |
| Cards | `rounded-xl` (**12px**), border `border` token | Image box, project cards |

---

## Page structure (single homepage)

Order and **anchor IDs** for menus (must match manifest):

1. **Hero** — no required anchor (top).
2. **Featured work** — CSS ID: `work`
3. **Campaigns / design** — CSS ID: `campaigns`
4. **Live production** — CSS ID: `live`
5. **Experience / career timeline** — CSS ID: `experience`
6. **Hybrid / edge** — CSS ID: `edge`
7. **Contact** — CSS ID: `contact`

**Navigation labels (desktop):** Work · Campaigns · Live · Path · Edge · Contact — links to `#work`, `#campaigns`, etc.

---

## Section accent colors (kickers)

| Section | Kicker color token |
|---------|--------------------|
| Hero | `mauve` |
| Work (01) | `mauve` |
| Campaigns (02) | `peach` |
| Live (03) | `teal` |
| Experience (04) | `mauve` |
| Hybrid (05) | `tokyo-blue` |
| Contact (06) | `mauve` |

---

## Motion & accessibility

- Source uses **Framer Motion** for reveals; optional in WordPress (animations off or subtle).
- Respect **`prefers-reduced-motion`** if adding CSS animations.
- Hero in code avoids harsh grain/flicker; prefer **slow** (20–48s) ambient motion if any.

---

## Elementor checklist

1. **Site Settings:** Global colors = table above; global fonts = JetBrains Mono; default radius ~12px.
2. **Home page:** One page, sections in order; **Advanced → CSS ID** on each anchored section.
3. **Header:** Sticky transparent → blurred/solid on scroll (approximate with header template + motion if available).
4. **Content:** Paste copy and media from `portfolio-content-manifest.json`.
5. **Video:** External MP4 URLs + poster images from manifest (Cloudinary).

---

*Derived from `src/index.css` and `tailwind/config` in creative-operator. Update this file if the React site tokens change.*
