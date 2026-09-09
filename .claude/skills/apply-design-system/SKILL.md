---
name: apply-design-system
description: Migrate one or multiple existing web pages to the project's current design system using the local Claude Design artifact, the real production codebase, and browser verification. Preserve functionality, consolidate shared components, ensure responsive and RTL/LTR correctness, and iterate until all supplied pages pass.
disable-model-invocation: true
---

# Apply Design System

## Purpose

Transform one or multiple existing application pages so they consistently follow the project's current design system.

This is an implementation and migration task.

Do not merely audit, describe, or recommend changes.

You must:

1. understand the design system;
2. inspect all supplied pages;
3. identify shared UI patterns;
4. reuse or create shared design-system components;
5. migrate the supplied pages;
6. preserve existing product behavior;
7. verify the result in the real browser;
8. fix responsive and visual issues;
9. test RTL/LTR when supported;
10. repeat until all supplied pages pass.

Do not stop after reporting problems.

---

# Inputs

Targets:

$ARGUMENTS

The input may contain one or multiple:

* full URLs;
* localhost URLs;
* staging URLs;
* development URLs;
* application routes;
* paths.

Targets may be provided:

* on separate lines;
* space-separated;
* as bullet points;
* as a mixed list of routes and URLs.

Never assume there is only one target.

Parse and normalize all supplied targets before implementation.

If only routes are supplied, discover the application's local base URL from the repository or running development environment.

Do not ask the user for information that can reasonably be discovered from the project.

---

# Primary Design System Source

The primary visual design-system reference is:

```text
.design/Design System.dc.html
```

Inspect this artifact before modifying any target page.

Use it to understand the intended:

* visual language;
* colors;
* typography;
* font hierarchy;
* spacing;
* containers;
* grids;
* borders;
* radii;
* shadows;
* icons;
* buttons;
* inputs;
* forms;
* cards;
* navigation;
* tables;
* badges;
* alerts;
* dialogs;
* drawers;
* dropdowns;
* loading states;
* empty states;
* interaction states;
* responsive patterns;
* RTL behavior;
* overall hierarchy.

Do not treat the `.dc.html` artifact as production application code.

Do not copy its generated HTML/CSS directly into the application unless a specific piece is already compatible with the project's architecture.

Translate its design intent into maintainable production components, tokens, styles, and layouts.

---

# Additional Design-System Sources

Also inspect existing production sources such as:

* `CLAUDE.md`;
* `.claude/design-system.md`;
* shared UI components;
* theme files;
* CSS variables;
* Tailwind configuration;
* design tokens;
* typography configuration;
* layout components;
* existing responsive utilities;
* shared forms;
* existing component libraries;
* Claude Design integration;
* synced Claude Design components when available.

The production codebase is the implementation source of truth.

The Claude Design artifact is the visual and design-intent source.

Do not create a parallel design system.

---

# Design System Sync Awareness

If Claude Design integration is available, inspect the current project setup.

Do not automatically run `/design-sync` unless it is clearly appropriate to the current state of the project.

Never sync legacy components into Claude Design merely because they exist.

Only treat components as design-system candidates when they represent the current intended design language.

The migration should first establish or improve the real production design system.

Future `/design-sync` operations can then expose those real production components back to Claude Design.

---

# Phase 1 — Understand the Project

Before changing code, identify:

* frontend framework;
* rendering architecture;
* routing system;
* styling approach;
* component architecture;
* shared component locations;
* design tokens;
* localization system;
* RTL support;
* responsive breakpoints;
* testing setup;
* browser automation setup;
* page layout conventions.

Respect the existing architecture.

Do not introduce a second styling or component system simply to complete the migration.

---

# Phase 2 — Inspect All Targets Before Editing

Before implementing the first page, inspect every supplied target.

Open or locate each target and understand:

* page hierarchy;
* sections;
* shared components;
* repeated patterns;
* navigation;
* cards;
* forms;
* filters;
* tables;
* dialogs;
* responsive behavior;
* shared layouts;
* loading states;
* empty states;
* error states.

Do not independently redesign each page.

Treat all supplied pages as one coordinated migration.

---

# Phase 3 — Identify Shared UI Patterns

Before making page-specific changes, identify repeated UI patterns across the targets.

Examples:

* page containers;
* page headers;
* section headers;
* buttons;
* cards;
* input groups;
* filters;
* search controls;
* tables;
* tabs;
* badges;
* status indicators;
* pagination;
* dialogs;
* drawers;
* breadcrumbs;
* empty states;
* loading states;
* mobile navigation.

Prefer solving a shared UI pattern once.

Use:

```text
shared component
→ reused across targets
```

instead of:

```text
page 1 custom implementation
page 2 custom implementation
page 3 custom implementation
```

The supplied pages must feel like one coherent product.

---

# Phase 4 — Build a Migration Map

For each target, internally map:

```text
Current UI
→
Design-system equivalent
```

Examples:

```text
Legacy button
→
Design-system Button

Custom card
→
Shared Card

Page-specific input
→
Shared Input

Legacy modal
→
Shared Dialog

Hard-coded color
→
Design token

Hard-coded spacing
→
Spacing token

Custom typography
→
Typography scale

Fixed-width layout
→
Responsive container

Duplicated mobile layout
→
Shared responsive layout
```

Do not output a large planning document unless it is genuinely useful.

Proceed with implementation once the migration strategy is clear.

---

# Phase 5 — Reuse Before Creating

Before creating a component, search the repository for an existing equivalent.

Preferred order:

```text
reuse existing component
→
extend existing component safely
→
create a reusable shared component
→
create page-specific implementation only when genuinely unique
```

Avoid duplicate:

* buttons;
* cards;
* inputs;
* dropdowns;
* modals;
* badges;
* tables;
* spacing rules;
* typography;
* colors;
* shadows;
* loading states;
* empty states.

Do not create a new component merely because the existing component API is slightly inconvenient.

Improve the shared abstraction when appropriate.

---

# Phase 6 — Use Real Design Tokens

Use existing project tokens wherever possible.

Prefer:

* CSS variables;
* theme tokens;
* Tailwind theme values;
* shared constants;
* design-system abstractions.

Avoid unnecessary hard-coded values such as:

```text
#1565C0
17px
23px
13px
box-shadow: ...
border-radius: 11px
```

when equivalent design-system values exist.

Do not create a second token system.

If the production project lacks required tokens but the design system clearly defines them, create reusable tokens in the appropriate central location rather than scattering values across pages.

---

# Phase 7 — Preserve Product Behavior

This task is a visual and structural migration.

Do not unintentionally change:

* business logic;
* API contracts;
* authentication;
* authorization;
* validation;
* routes;
* navigation behavior;
* analytics;
* tracking events;
* SEO;
* accessibility;
* feature flags;
* payment logic;
* permissions;
* localization keys;
* user-facing data behavior;
* loading behavior;
* submission behavior.

If existing behavior appears incorrect, do not silently redesign it unless fixing it is necessary for the page to function.

Separate design migration from unrelated product changes.

---

# Phase 8 — Implement the Migration

Apply the new design consistently across each supplied page.

Inspect and migrate as applicable:

* page background;
* main containers;
* navigation;
* page header;
* title;
* subtitle;
* breadcrumbs;
* section structure;
* cards;
* lists;
* forms;
* buttons;
* icons;
* filters;
* search;
* tabs;
* tables;
* pagination;
* status badges;
* alerts;
* dialogs;
* drawers;
* empty states;
* error states;
* loading states.

Do not modernize one small section while leaving directly related legacy sections visually inconsistent.

At the same time, do not modify unrelated application areas unnecessarily.

---

# Phase 9 — Maintain Visual Hierarchy

Ensure that each migrated page correctly expresses:

* primary actions;
* secondary actions;
* page hierarchy;
* section hierarchy;
* information grouping;
* content density;
* emphasis;
* spacing rhythm;
* consistent typography;
* clear interactive states.

Do not reproduce a screenshot mechanically.

Implement the intended visual hierarchy in a reusable way.

---

# Phase 10 — Responsive Verification

Responsive verification is mandatory.

At minimum test:

* 1440x900
* 1280x800
* 1024x768
* 768x1024
* 430x932
* 390x844
* 375x667

Also identify the project's real responsive breakpoints.

Test immediately below and above important breakpoints.

Examples when applicable:

* 639 / 640
* 767 / 768
* 1023 / 1024
* 1279 / 1280

The project's actual breakpoints take precedence.

---

# Responsive Audit Checklist

For each target and relevant viewport verify:

* no unintended horizontal scrolling;
* no content outside the viewport;
* no clipped content;
* no overlapping elements;
* correct stacking;
* correct grid behavior;
* correct flex behavior;
* consistent spacing;
* correct alignment;
* readable typography;
* sensible line wrapping;
* correct truncation;
* correct image sizing;
* preserved media aspect ratios;
* usable buttons;
* appropriate touch targets;
* correct navigation;
* usable forms;
* correctly positioned dropdowns;
* correctly sized dialogs;
* correctly sized drawers;
* usable tables;
* correct sticky behavior;
* correct fixed positioning;
* no sudden breakpoint layout failures;
* no unusable whitespace;
* no hidden important actions.

When useful, explicitly check:

```javascript
document.documentElement.scrollWidth > window.innerWidth
```

If horizontal overflow exists, identify the exact offending element.

Do not hide the symptom.

Fix the layout root cause.

---

# Phase 11 — Responsive Root-Cause Rules

Prefer:

* natural document flow;
* Flexbox;
* CSS Grid;
* flexible dimensions;
* responsive containers;
* sensible `max-width`;
* correct wrapping;
* `min-width: 0` where genuinely needed;
* shared breakpoint behavior;
* logical CSS properties.

Avoid using these as shortcuts:

* excessive media queries;
* arbitrary pixel patches;
* fixed device-specific widths;
* unnecessary absolute positioning;
* duplicated desktop/mobile layouts;
* global `overflow: hidden`;
* hiding content that does not fit;
* arbitrary z-index increases.

Do not make the CSS progressively harder to maintain just to pass a viewport.

---

# Phase 12 — RTL and LTR

If the project supports both directions, test them independently.

When available, use Arabic and English as representative locales.

Verify:

* text alignment;
* page structure;
* component ordering;
* grid ordering;
* flex ordering;
* navigation;
* directional icons;
* arrows;
* breadcrumbs;
* margins;
* padding;
* form icons;
* input alignment;
* dropdown positioning;
* tables;
* pagination;
* mixed text and numbers.

Prefer logical properties such as:

```text
margin-inline
padding-inline
inset-inline
border-inline
```

where appropriate.

Avoid duplicated RTL-specific layouts unless technically necessary.

---

# Phase 13 — Interaction Testing

Do not validate only static screenshots.

Test relevant interactions.

Examples:

* desktop navigation;
* mobile navigation;
* dropdowns;
* menus;
* dialogs;
* drawers;
* tabs;
* accordions;
* forms;
* validation;
* filters;
* search;
* pagination;
* carousels;
* sticky controls;
* expandable sections;
* scrolling;
* loading states.

A page that looks correct initially but breaks after interaction does not pass.

---

# Phase 14 — UI States

Where applicable, verify:

* initial state;
* loading state;
* populated state;
* empty state;
* error state;
* disabled state;
* selected state;
* expanded state;
* collapsed state;
* dialog state;
* drawer state;
* validation state.

Do not leave legacy styling in secondary states.

---

# Phase 15 — Content Stress Testing

Do not optimize only for current sample data.

Where relevant verify behavior with:

* short text;
* normal text;
* long text;
* multiline text;
* very long titles;
* long translated text;
* long unbroken values;
* large prices;
* large numbers;
* missing images;
* missing optional fields;
* many list items;
* zero list items.

Avoid fixes that depend on one specific sample value.

---

# Phase 16 — Browser Verification

Use Chrome/browser integration whenever available.

For every migrated target:

1. open the real page;
2. inspect the rendered UI;
3. inspect the DOM;
4. inspect relevant computed styles when useful;
5. inspect the browser console;
6. inspect relevant network requests;
7. test interactions;
8. test responsive behavior;
9. test RTL/LTR when applicable.

Do not rely only on source-code inspection.

Do not rely only on build success.

Do not rely only on automated tests.

The real rendered page must be verified.

---

# Phase 17 — Browser Debugging

During verification, investigate:

* runtime errors;
* hydration errors;
* JavaScript exceptions;
* failed resources;
* rendering warnings;
* failed API calls relevant to the page;
* broken images;
* broken fonts;
* unexpected layout shifts;
* inaccessible overlays;
* stale styles;
* CSS collisions.

Do not ignore browser-console errors merely because the page appears visually acceptable.

---

# Phase 18 — Shared Component Safety

If a target issue originates from a shared component:

1. find representative usages;
2. understand current dependencies;
3. implement the root-cause fix;
4. verify the target;
5. perform a focused regression check on other important usages.

Do not fix one page by breaking another.

When the new design system intentionally changes all usages, apply the change consistently.

---

# Phase 19 — Iterative Fix Loop

Use this loop continuously:

```text
Inspect design system
↓
Inspect all supplied pages
↓
Identify shared patterns
↓
Implement shared foundations
↓
Migrate page
↓
Open in Chrome
↓
Compare and inspect
↓
Find visual or responsive issue
↓
Fix root cause
↓
Retest
↓
Repeat
```

Do not stop after the first visual improvement.

Continue until no known migration issue remains.

---

# Phase 20 — Page Ordering

When several pages are supplied, choose an implementation order that maximizes reuse.

Prefer migrating foundational or component-rich pages first when that produces reusable components for later pages.

Example:

```text
shared layout
↓
shared navigation
↓
forms/components
↓
complex representative page
↓
simpler pages
```

Do not blindly process URLs in input order if a better dependency order exists.

Still ensure every supplied page is completed.

---

# Phase 21 — Avoid Repeated Work

When a shared issue is discovered on several supplied pages, fix the shared source once.

Do not patch every page independently.

Examples:

```text
bad shared Card
→ fix Card

bad container width
→ fix shared layout/container

bad Button spacing
→ fix Button

RTL issue in Select
→ fix shared Select
```

Only use page-specific overrides for genuinely page-specific needs.

---

# Phase 22 — Automated Verification

Run the project's relevant existing checks after code changes.

Use whatever already exists, such as:

* lint;
* type checking;
* build;
* unit tests;
* integration tests;
* frontend tests;
* component tests;
* browser tests;
* Playwright;
* Cypress.

Do not bypass failing checks.

Do not introduce unrelated testing tools solely to finish the migration.

---

# Phase 23 — Regression Protection

When the migration uncovers a meaningful bug that could recur, add or improve an automated regression test when practical.

Preferred flow:

```text
Discover issue in Chrome
↓
Reproduce reliably
↓
Fix root cause
↓
Verify in Chrome
↓
Add regression test when valuable
↓
Run relevant checks
↓
Final browser verification
```

Do not create low-value snapshot noise simply to claim test coverage.

---

# Phase 24 — Accessibility

Preserve or improve accessibility during the migration.

Verify relevant:

* semantic structure;
* labels;
* focus states;
* keyboard access;
* contrast;
* button semantics;
* links;
* form errors;
* dialog behavior;
* visible focus indicators.

Do not remove accessibility behavior for visual convenience.

---

# Phase 25 — Design Consistency Across Targets

After individual pages pass, perform a final cross-page consistency review.

Compare supplied pages for:

* typography;
* buttons;
* spacing;
* cards;
* borders;
* shadows;
* containers;
* forms;
* navigation;
* responsive behavior;
* status styles;
* empty states.

The pages must look like one system, not several independently redesigned screens.

---

# Phase 26 — What Not To Do

Do not:

* copy the `.dc.html` page wholesale into production;
* create a second design system;
* duplicate existing components unnecessarily;
* replace functioning business logic during a visual migration;
* add large amounts of arbitrary CSS overrides;
* hide responsive bugs with `overflow: hidden`;
* use many magic numbers;
* create per-device layouts without need;
* stop after desktop looks correct;
* skip mobile verification;
* skip RTL when the project supports it;
* skip browser verification;
* declare success because the build passes;
* silently ignore console errors;
* leave supplied pages partially migrated without stating it.

---

# Completion Criteria

The migration is complete only when all supplied targets satisfy the following:

* each page follows the current design system;
* the Claude Design artifact has been respected as the visual reference;
* production architecture remains maintainable;
* shared components have been reused appropriately;
* duplicate legacy UI has been removed where appropriate;
* visual hierarchy is consistent;
* business functionality remains intact;
* relevant interactions work;
* important UI states work;
* responsive verification passes;
* important breakpoint boundaries pass;
* RTL/LTR passes when supported;
* horizontal overflow is absent unless intentionally required;
* no new relevant browser-console errors remain;
* relevant automated checks pass;
* all supplied pages were verified in the real browser.

Do not claim full completion when browser verification was not possible.

---

# External Blockers

If verification is blocked by something outside the codebase, such as:

* unavailable backend;
* missing credentials;
* inaccessible environment;
* third-party outage;
* missing test data;

continue as far as possible.

Clearly distinguish:

```text
completed implementation
```

from:

```text
blocked verification
```

Do not claim `PASS` for unverified areas.

---

# Final Report

Keep the final response concise.

Use this structure:

## Result

`PASS` or `PARTIAL`

## Pages

List the supplied pages that were migrated.

## Shared System

Summarize important shared components, tokens, or foundations created or reused.

## Fixed

Summarize the meaningful design, responsive, RTL/LTR, or interaction issues fixed.

## Verification

Summarize:

* browser verification;
* viewport coverage;
* RTL/LTR coverage;
* relevant automated checks.

## Remaining

Only include this section when a genuine blocker or unresolved problem remains.
