**# Price Table — Editor Guide**

This guide explains how to create and manage pricing comparison tables on the website.

You do **not** need any technical knowledge. Everything is managed from the WordPress admin.

---

**## 1. Overview**

The Price Table system lets you build side-by-side comparison tables (for example: Basis / Groei / Pro).

It uses three parts:

| Part | What it is | Simple meaning |

|------|------------|----------------|

| **Table Structure** | The blueprint of the table | Defines **what** is compared (sections and rows) |

| **Package** | One plan or product | Holds the **values** for that plan (desktop **and** mobile) |

| **Price Table block** | Placed on a page | Chooses which Structure and Packages to show |

**In short:**

- **Structure** = what the table looks at / compares

- **Package** = the actual values for a particular plan

- **Block** = the table you place on a page

**Desktop and mobile are different layouts:**

- **Desktop** shows the full comparison grid (features as rows, packages as columns).

- **Mobile** shows one card per package (short description + expandable feature list).

Both layouts use the same Structure and Packages. Mobile needs some extra Package fields (see [Mobile Values](#mobile-values)).

---

**## 2. Table Structure**

A Structure defines the rows of your **desktop** comparison table: section headings and feature labels.

**### Where to find it**

In the WordPress admin menu, open **Pricing Structures**.

**### Create a new Structure**

1. Go to **Pricing Structures → Add Pricing Table Structure**.

2. Enter a clear title (for example: `Website onderhoud vergelijking`).

3. Add sections and items (see below).

4. Click **Publish**.

**### Add sections**

1. Under **Sections**, click **Add Section**.

2. Enter a **Section Heading** (required).

   Example: `Hosting en mail`, `Onderhoud`, `Support`.

You can add as many sections as you need.

**Important:** Section headings appear as full-width bands in the desktop table. The order of sections here is the order visitors see on desktop.

**### Add items (comparison rows)**

Inside each section:

1. Click **Add Item**.

2. Fill in:

   - **Item Label** — the text shown in the left column (example: `Gratis verhuizing/migratie`)

   - **Value Type** — how package values for this row should work (see below)

   - **Label Tooltip** (optional) — extra explanation next to the label (shows as a small **i** on the website)

   - **Default Icon** — only appears when Value Type is **Icon with Tooltip**

**### Reorder sections and items**

- Drag sections up or down to change section order.

- Drag items up or down within a section to change row order.

**The Structure controls the order of the desktop table.** Packages follow this order automatically for Desktop Values.

**### Item types (Value Type)**

Choose the type that matches how packages should fill that row on **desktop**:

| Value Type | What it means | Typical use |

|------------|---------------|-------------|

| **Icon with Tooltip** | Shows an icon. Optional tooltip appears when hovering the icon. | Checkmarks / included features |

| **Plain text** | Shows simple text only. | Numbers or short values (`1 · 2 GB`) |

| **Plain text with tooltip** | Shows text; optional tooltip on hover. | Values that need a short explanation |

| **Text with link** | Shows link text, with an optional clickable link icon. | Links to more info or a form |

**### Label Tooltip**

Optional help text next to the **feature name** (left column on desktop).

Example:

- Label: `Mailboxen op je domein`

- Label Tooltip: `E-mail zoals mail@jouwdomein.nl, inclusief webmail.`

On the website, visitors see a small **i** next to the label.

**### Default Icon**

Only for **Icon with Tooltip** items.

- Sets the starting icon used when Packages are created or updated from this Structure.

- If you leave it empty, a standard circle icon is used automatically.

- Each Package can later replace this with its own icon (see Packages).

**### Simple Structure example**

```

Structure: Website onderhoud

├── Section: Hosting en mail

│     ├── Hostingpakket          (Plain text with tooltip)

│     ├── Mailboxen op je domein (Plain text + Label Tooltip)

│     └── Gratis verhuizing      (Icon with Tooltip)

└── Section: Onderhoud

      ├── Backups en updates     (Icon with Tooltip)

      └── Testomgeving           (Icon with Tooltip)

```

---

**## 3. Package**

A Package is one plan: price, button, desktop cell values, and mobile card content.

**### Where to find it**

In the WordPress admin menu, open **Pricing Packages**.

**### Create a new Package**

1. Go to **Pricing Packages → Add Pricing Package**.

2. Enter an internal title (for your own organisation; visitors mainly see the Price Title below).

3. Fill in the package header fields.

4. Select a **Structure**.

5. Open the **Desktop Values** tab and fill in the generated items.

6. Open the **Mobile Values** tab and fill in the mobile card content.

7. Click **Publish**.

**### Package header fields**

| Field | What it does |

|-------|--------------|

| **Title** (inside Price) | Name shown above the price (example: `Site Basis`) |

| **Currency** | Currency symbol (default: `€`) |

| **Price** | Amount shown (example: `49`) |

| **Price Subtext** | Small text under the price (default: `per maand, excl. btw`) |

| **Button** | Call-to-action button (text + URL + open in same/new tab) |

| **Featured** | Highlights this package (desktop column + mobile card). Featured packages also open their mobile feature list by default and can show an “Aanbevolen” badge. |

| **Structure** | Which Structure this package belongs to (**required**) |

Title, price, subtext, button, and Featured are shared on **both** desktop and mobile.

**### Select a Structure**

Under **Structure**, choose the Pricing Structure this package uses.

After you select a Structure:

- The **Desktop Items** list is filled automatically from that Structure.

- You get one desktop item row for every Structure item, in the same order.

- You **cannot** manually add, remove, or reorder Desktop Items. That is controlled by the Structure.

---

**### Desktop Values**

On the Package edit screen, open the **Desktop Values** tab.

These values fill the **desktop comparison table** columns.

Each item shows its label and type at the top. Below that, only the fields needed for that type appear:

**#### Icon with Tooltip**

- **Icon Override** — the icon shown in this package’s cell (starts from the Structure default; you can change it).

- **Tooltip Text** — optional text shown when hovering the icon.

If there is no icon, the table shows a dash (`—`).

**#### Plain text**

- **Value** — the text shown in the cell.

**#### Plain text with tooltip**

- **Value** — the text shown in the cell.

- **Tooltip Text** — optional explanation on hover.

**#### Text with link**

Uses the **Link** field only:

- **Link text** — the words shown in the table.

- **URL** — where the link goes.

- **Open in** — same tab or new tab.

**On the website:**

- The **link text** is always shown (if filled in).

- The small **link icon** appears only when a **URL** is filled in.

- If link text is empty, the cell shows a dash (`—`).

---

**### Mobile Values**

On the Package edit screen, open the **Mobile Values** tab.

Mobile does **not** reuse the desktop grid. Each package becomes a **card** with:

- Name, price, button (from the header fields above)

- A short subtitle

- An optional badge

- An expandable list (“Bekijk wat erin zit” by default)

| Field | What it does |

|-------|--------------|

| **Subtitle** | Short line under the package name on mobile. Example: `Veilig online: backups, updates, beveiliging · Lite hosting`. This is **not** the same as Price Subtext. |

| **Badge** | Optional label next to the name (e.g. `Aanbevolen`). Leave empty to use **Aanbevolen** automatically when Featured is on. Leave Featured off and Badge empty to hide the badge. |

| **Details Label** | Text for the expandable section (default: `Bekijk wat erin zit`). |

| **Feature List** | Curated bullets shown inside the expandable section. |

**#### Feature List**

Click **Add Feature** for each mobile bullet.

| Field | What it does |

|-------|--------------|

| **Feature Text** | The bullet text visitors see (required). |

| **Icon Style** | **Check**, **Plus**, or **Custom Icon**. |

| **Custom Icon** | Appears only when Icon Style is **Custom Icon**. Upload/select an image. |

| **Related Desktop Item** | Optional. Link this bullet to a Structure item so **Exclude Items** on the block can also hide this mobile bullet. Leave empty to always show this bullet. |

**Icon Style options:**

- **Check** — standard checkmark (included feature).

- **Plus** — plus icon (often used for extras vs a lower package).

- **Custom Icon** — your own image. If Custom Icon is selected but no image is uploaded, a dash (`—`) is shown instead of a broken image.

**Tips:**

- Mobile feature order is the order you set in this list (independent of desktop Structure order).

- You may combine several desktop ideas into one friendly mobile line (example: `Lite hosting · 1 mailbox (2 GB)`).

- Higher packages often start with a line like `Alles uit Site Basis`.

---

**### Packages keep their own values**

Editing values in a Package does **not** change the Structure.

- Structure = desktop labels, types, order, default icon, label tooltips

- Package Desktop Values = this plan’s comparison cell values

- Package Mobile Values = this plan’s mobile card text and feature list

So you can give each package different desktop cells **and** different mobile cards.

**### Changing the Structure on a Package**

If you switch a Package to a **different** Structure:

1. The **Desktop Items** list is rebuilt to match the new Structure.

2. Desktop values for matching items are kept where possible.

3. Desktop items that no longer exist in the new Structure disappear from this package’s desktop list.

4. New desktop items appear empty (or with the Structure’s default icon for icon rows).

5. **Mobile Values are not auto-rebuilt** from the Structure — update the mobile Feature List yourself if needed. Related Desktop Item choices will follow the new Structure.

**Important:** Only change Structure when you intend to. Prefer creating a new Package if the plan belongs to a different comparison.

---

**## 4. Price Table Block**

This is what you place on a page to show the comparison table.

**### Add the block**

1. Edit the page (or template) in the WordPress block editor.

2. Add a block and search for **Pricing Table**.

3. Insert it.

**### Block settings**

**#### 1. Table Structure**

Select the Structure this table should use.

After you choose it:

- Available Packages update to packages that use that Structure.

- Exclude Items choices update to that Structure’s items.

**#### 2. Available Packages**

Select which packages appear as columns (desktop) / cards (mobile).

**Limits (current system):**

- Minimum: **1** package

- Maximum: **5** packages

Only packages linked to the selected Structure are listed.

**#### 3. Exclude Items**

Optional. Choose comparison items to **hide on this table only**.

**Important:**

- Excluding an item **only hides it** on this particular Price Table block.

- It does **not** delete the item from the Structure.

- It does **not** change or delete any Package values.

- Other pages/tables that use the same Structure and Packages can still show the item.

**Desktop:** excluded items (and whole sections if every item in a section is excluded) are hidden from the grid.

**Mobile:** a Feature List bullet is hidden only if it has a **Related Desktop Item** that was excluded. Bullets with no related item stay visible.

**#### 4. Spacing (Margin and Padding)**

In the block sidebar (usually under **Styles** / **Dimensions**, like other WordPress blocks):

- Set **Margin** around the whole Price Table.

- Set **Padding** inside the outer Price Table container.

These apply to the **outer block only** — not to individual rows, sections, or package cards.

If the editor shows different values for Desktop / Tablet / Mobile, you can use those; otherwise one set of values is used.

---

**## 5. How Structure, Package and Block work together**

```

Table Structure

→ Defines desktop sections and comparison items (labels, types, order)

Package

→ Desktop Values = cell values for the comparison grid

→ Mobile Values = subtitle, badge, expandable feature list

→ Shared = title, price, button, Featured

Price Table Block

→ Chooses the Structure + Packages

→ Optionally hides selected items (desktop + linked mobile bullets)

→ Optional margin / padding on the outer block

→ Displays desktop grid or mobile cards depending on screen size

```

Think of it like this:

- Structure = row titles for the desktop spreadsheet

- Package = one filled-in column **plus** its own mobile card text

- Block = which packages to show on this page, and outer spacing

---

**## 6. Common tasks / examples**

**### Create a new pricing table**

1. Create (or reuse) a **Pricing Structure** with sections and items.

2. Create **Pricing Packages** and select that Structure for each package.

3. Fill in each package’s price, button, **Desktop Values**, and **Mobile Values**.

4. Edit the page and add a **Pricing Table** block.

5. Select the **Table Structure**.

6. Select the **Packages** (up to 5).

7. Optionally **Exclude Items** you do not want on this page.

8. Optionally set **Margin** / **Padding** on the block.

9. **Update / Publish** the page.

**### Change the text shown for a package (desktop)**

1. Go to **Pricing Packages**.

2. Open the package.

3. Change the relevant field:

   - Plan name → **Title** (in Price)

   - Amount → **Price**

   - Small line under price → **Price Subtext**

   - Button → **Button**

   - Cell content → **Desktop Values** tab

4. Update the package.

**### Change the mobile card for a package**

1. Open the Package.

2. Open the **Mobile Values** tab.

3. Edit **Subtitle**, **Badge**, **Details Label**, and/or the **Feature List**.

4. Update the package.

**### Add a custom icon on a mobile feature**

1. Open the Package → **Mobile Values**.

2. Edit (or add) a Feature List row.

3. Set **Icon Style** to **Custom Icon**.

4. Upload or select an image in **Custom Icon**.

5. Update the package.

**### Hide one comparison item from one table**

1. Edit the page that contains the Price Table block.

2. Select the block.

3. Under **Exclude Items**, select the item to hide.

4. Update the page.

The item stays available in the Structure and in all Packages.

On mobile, only Feature List bullets linked to that item via **Related Desktop Item** are also hidden.

**### Show the item again**

1. Edit the same Price Table block.

2. Remove the item from **Exclude Items**.

3. Update the page.

Package data was never removed, so values appear again immediately.

**### Change the order of desktop rows**

1. Edit the **Pricing Structure**.

2. Drag sections / items into the desired order.

3. Update the Structure.

**### Change the order of mobile bullets**

1. Edit the **Package** → **Mobile Values**.

2. Reorder the **Feature List** rows.

3. Update the Package.

**### Change a feature label for everyone (desktop)**

1. Edit the **Pricing Structure**.

2. Change the **Item Label**.

3. Update.

All packages and tables using that Structure will show the new desktop label. Existing package values for that item stay linked.

**### Adjust space around the table on a page**

1. Edit the page and select the **Pricing Table** block.

2. Open the block’s style / dimensions controls.

3. Set **Margin** and/or **Padding**.

4. Update the page.

---

**## 7. Important rules / things to know**

- **Structure controls desktop order.** Reorder rows in the Structure, not under Desktop Values.

- **Do not try to reorder Desktop Items** on the Package. Dragging is disabled there on purpose.

- **Do not try to add/remove Desktop Items** manually. The list is generated from the Structure.

- **Mobile Feature List is edited on the Package** and has its own order.

- **Package values belong to the Package.** Changing one package does not change another.

- **Block exclusions are display-only.** They hide items on that one table; they do not delete data.

- **Packages must match the Structure.** A package only appears in the block’s package list if it uses the selected Structure.

- **Maximum 5 packages** per Price Table block.

- **Featured** highlights the package on desktop and mobile; featured mobile cards open their feature list by default.

- Empty desktop cells (or missing custom mobile icons) show a dash (`—`).

- **Margin / Padding** on the block affect only the outer container.

- After editing Structures or Packages, refresh or update the page that contains the block if the preview looks outdated.

- Changing a Package’s Structure rebuilds its Desktop Items list. Do this carefully.

---

**## 8. Troubleshooting**

**### Package value is not showing (desktop)**

Check:

1. Did you **Update** the Package after editing?

2. Are you looking under the **Desktop Values** tab?

3. Is the correct field filled for that item type?

   - Plain text → **Value**

   - Icon → **Icon Override**

   - Text with link → **Link text** (and URL if you want the icon)

4. Is this item **excluded** in the Price Table block?

5. Does the Package use the **same Structure** as the block?

**### Mobile card looks empty or incomplete**

Check:

1. Did you fill the **Mobile Values** tab (Subtitle + Feature List)?

2. Did you **Update** the Package?

3. Are you viewing the page on a narrow/mobile width? Desktop still shows the grid.

4. If a feature uses **Custom Icon**, did you upload an image?

**### Custom mobile icon is not showing**

Check:

1. **Icon Style** is set to **Custom Icon**.

2. An image is selected in **Custom Icon**.

3. The Package was updated.

4. Without an image, a dash (`—`) is shown on purpose.

**### Item is missing from the desktop table**

Check:

1. Does the item still exist in the **Structure**?

2. Is it listed under **Exclude Items** on this block?

3. Were **all** items in that section excluded? (Then the whole section is hidden.)

4. Did you select the correct Structure on the block?

**### Mobile bullet disappeared after excluding an item**

Check:

1. Does that Feature List row have a **Related Desktop Item** set?

2. Is that related item in **Exclude Items** on the block?

3. To keep the mobile bullet while hiding the desktop row, clear **Related Desktop Item** on that feature (or stop excluding the item).

**### Package is not appearing as an option**

Check:

1. Is the Package **published** (or at least saved)?

2. Does the Package’s **Structure** match the Structure selected in the block?

3. Have you already selected **5 packages**? (Maximum reached.)

**### Link icon is not appearing**

For **Text with link** desktop items:

1. Open the Package → **Desktop Values**.

2. Check the **Link** field has a **URL**.

3. Check **Link text** is filled (text can show without URL; the icon needs the URL).

**### Tooltip is not appearing**

Check:

1. For feature-name help: is **Label Tooltip** filled on the Structure item?

2. For cell tooltips: is **Tooltip Text** filled on the Package **Desktop Values** item?

3. Is the item type one that supports tooltips?

   - Icon with Tooltip

   - Plain text with tooltip

   - (Label Tooltip works for any type on the left-hand label)

4. Hover the icon / underlined text / **i** on the live page (not only a quick glance at the editor).

**### An item was excluded from the block**

1. Edit the page.

2. Select the Pricing Table block.

3. Look at **Exclude Items**.

4. Remove the item from the exclude list and update the page.

**### Spacing (margin/padding) is not changing**

Check:

1. You selected the **Pricing Table** block (not an inner element).

2. You changed **Margin** / **Padding** in the block style / dimensions controls.

3. You **Updated** the page.

4. Hard-refresh the browser if the old spacing still appears.

**### Structure / package changes are not reflected on the page**

Check:

1. Did you click **Update** on the Structure or Package?

2. Did you **Update** the page that contains the block?

3. Try a hard refresh in the browser (cache).

4. Confirm the block still points to the Structure and Packages you edited.

If everything above looks correct and the problem remains, contact a developer with:

- The page URL

- The Structure name

- The Package name(s)

- What you expected vs what you see

---

**## Quick reminder**

| I want to… | Edit this |

|------------|-----------|

| Change desktop row labels / order / types | **Pricing Structure** |

| Change a plan’s price, button, or desktop cell values | **Pricing Package → Desktop Values** |

| Change mobile subtitle / feature list / custom icons | **Pricing Package → Mobile Values** |

| Choose which plans appear on a page | **Price Table block** |

| Hide a desktop row (and linked mobile bullets) on one page | **Exclude Items** on the block |

| Add space around the table | **Margin / Padding** on the block |
