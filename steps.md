# OneShop - History of Changes (steps)

This file tracks key implementation steps in chronological order.
Use it as a lightweight project timeline.

## 2026-04-18

### 1. Localization and UI text cleanup
- Extended language files for Czech and English across storefront and admin areas.
- Localized admin CRUD views for categories, customers, price lists, shipping methods, payment methods, payment transactions, and orders.
- Added validation and auth translation files.
- Replaced hardcoded flash/validation strings in controllers and services with translation keys.

### 2. Product detail template repair
- Rebuilt damaged product detail template for the default theme.
- Restored breadcrumb, pricing, add-to-cart, and related products blocks.

### 3. Git repository setup
- Improved .gitignore for Laravel + Windows workflow.
- Prepared project for clean initialization and first push.

### 4. Product image system (private storage + secure rendering)
- Added product image management in admin (upload, alt, sorting, main image, delete).
- Stored files outside public directory using private_products disk.
- Served images through Laravel route/controller only.
- Added runtime watermark rendering service (original files remain unchanged).
- Added placeholder image endpoint for products without images.
- Updated default/studio/mono storefront cards and product detail pages for image fallback.

### 5. Shared-stock domain model (one stock card -> many product cards)
- Added stock_items model/table as source of truth for physical inventory.
- Extended products with stock_item_id, price, active, visibility.
- Added compatibility_models and product_compatibilities for model-to-product mapping.
- Added inventory service for order-time stock deduction from stock_items.

### 6. Seeders for shared stock and SEO landing cards
- Added SharedStockCatalogSeeder:
  - Created stock cards for battery, charger, and battery+charger set.
  - Created multiple product cards linked to shared stock cards.
  - Created compatibility model mappings.
- Added ModelSeoLandingSeeder:
  - Created SEO-oriented product cards for notebook models.
  - Reused existing physical stock cards (no inventory duplication).

### 7. Product composition visibility in admin
- Added stock_item_components table and StockItemComponent model.
- Added composition relations to StockItem.
- Updated product admin form to display product composition:
  - Primary stock card
  - Kit components with required quantity and current component stock
- Seeded kit composition data for demo set products.

### 8. Product composition editing + order-time stock deduction
- Added admin composition editing for products:
  - Add component to kit
  - Update component quantity
  - Remove component
- Added validation guard for `battery_kit` products so they must keep at least one component.
- Added computed max sellable quantity display for kit products in admin.
- Extended inventory deduction logic:
  - Deducts direct product stock card
  - For kits, also deducts each component stock card according to composition ratio
- Integrated automatic stock deduction into order creation flow (`OrderService`).

## How to update this file
- Append a new dated section for each work session.
- Keep entries short and factual.
- Focus on implemented changes, migrations, seeders, and behavior-impacting updates.
