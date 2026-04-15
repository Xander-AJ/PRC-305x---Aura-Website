# PRC 305 - Aura Energy Website

WordPress + Elementor submission project for **PRC 305: Web Design and Content Management Systems for PR** (Jan-Apr 2026).

## Project Summary

Aura Energy is a premium functional beverage concept targeted at professionals, creators, and shift workers in East Africa.  
This website delivers:

- Clear brand story and product positioning
- Structured multi-page navigation
- Responsive design for mobile, tablet, and desktop
- Working contact flow via MetForm
- Social section with launch-state messaging

## Course Requirement Mapping

- **Home page**: branding, hero messaging, CTA flow
- **About page**: background and context
- **Services/Products page**: product lineup and key details
- **Contact page**: form-based contact with validation
- **Social media integration**: icon section included (accounts pending launch)
- **Responsive design**: Elementor + custom CSS polish pass

## Stack

- WordPress (local XAMPP during development)
- Elementor
- Astra theme
- MetForm (contact form)
- Cloudinary-hosted media assets

## Repository Structure

- `elementor-templates/` - Aura page templates and import instructions
- `scripts/aura-prc305-setup.php` - one-command setup/sync script for pages, form, kit, and CSS
- `STYLE_GUIDE.md` - visual system (palette, typography, layout principles)
- `PRC305-Aura-Final-Presentation.pptx` - final presentation deck

## Local Run (XAMPP)

1. Start Apache + MySQL in XAMPP.
2. Open `http://localhost/wordpress/`.
3. Admin: `http://localhost/wordpress/wp-admin`.
4. To sync Aura pages/templates/form:

```bash
/Applications/XAMPP/xamppfiles/bin/php /Applications/XAMPP/xamppfiles/htdocs/wordpress/scripts/aura-prc305-setup.php
```

## Presentation/Demo Checklist

- Home -> About -> Products -> Contact walkthrough
- Mobile responsiveness check in browser dev tools
- Contact form tests:
  - Empty submit (required validation)
  - Invalid email format
  - Valid submission success message
  - Confirm entry in MetForm submissions

## Notes

- Social URLs are intentionally in **launch-pending state** until official Aura accounts are created.
- For production deployment, configure SMTP (for reliable form email) and update site URL/domain settings.
