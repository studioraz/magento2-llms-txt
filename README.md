# Studio Raz Magento 2 LLMs.txt Module

## Overview

This module generates a standardized `llms.txt` file in markdown format for Magento 2 stores, containing AI/LLM-friendly documentation and project structure information, to help large language models better understand your store for integration, automation, or support.

---

## Features

- **Automatic Generation** of `llms.txt` files reflecting the current store context.
- **Standardized Markdown Output** including store name, homepage meta title, categories, featured products, CMS pages, and more.
- **AI/LLM Documentation**: Allows AI tools and language models to easily ingest and comprehend your Magento 2 store's structure.
- **Admin Interface Integration**: Generate or copy the file directly from Magento Admin configuration.
- **Integration Awareness**: If modules like `SR_PointOfSale` or `Mirasvit_Feed` are installed, point-of-sale contacts & product feed URLs will be included.
- **Customizable Content**: Option to override with manual content.
- **Frontend Template**: View generated documentation on your store (for debugging or preview).

---

## Installation

1. **Install Module with Composer:**
   ```sh
   composer require studioraz/magento2-llms-txt
   ```

2. **Enable the Module:**
   ```sh
   bin/magento module:enable SR_LlmsTxt
   bin/magento setup:upgrade
   ```

---

## Activation & Usage

1. **Admin Configuration:**
   - Go to **Stores > Configuration > Studio Raz > LLMs.txt** in Magento Admin.
   - Use the **"Generate Content"** button to auto-generate the documentation file for your store.
   - You may **edit the generated content manually** if desired.
   - Click **"Copy to Clipboard"** for easy transfer of content.

2. **Frontend Access (for preview/debug):**
   - The generated markdown is also available as a frontend template for preview (location configurable).

3. **Automatic Content (generated):**
   - Store name and homepage meta title
   - List of active CMS/informational pages (with links)
   - Main categories (with descriptions & URLs)
   - Featured products (bestsellers) with their descriptions & URLs
   - Product feed links (when supported modules are installed)
   - Point of sale locations (if applicable)
   - Account & order support links

---

## Advanced Features

- **Product Feeds**: If using `Mirasvit_Feed`, product feed URLs (Google, Facebook, etc.) are included.
- **Point of Sale Locations**: If `SR_PointOfSale` is installed, POS contact details are shown.

---

## Support

For Studio Raz support, please contact [support@studioraz.co.il](mailto:support@studioraz.co.il).

---

## License

Copyright © 2025 Studio Raz. All rights reserved.
See `LICENSE.txt` for details.
