# Browser & UI Verification Rules

For every user-facing web change, do not consider the task complete after code changes, static analysis, build success, or automated tests alone.

When browser access is available, browser verification is mandatory.

## Required Verification Flow

For every frontend or user-facing change:

1. Run the relevant static analysis, type checks, linting, and automated tests.
2. Start the application if needed.
3. Open the affected page or flow in Chrome.
4. Reproduce the real user journey.
5. Inspect the browser console for errors and warnings.
6. Inspect relevant network requests and responses.
7. Inspect the DOM and actual rendered UI state.
8. Test relevant interactions.
9. Test responsive behavior.
10. Fix every issue found.
11. Repeat the browser verification after fixes.
12. Do not mark the task complete until the real browser flow works correctly.

Automated tests and browser verification are complementary. Neither replaces the other.

---

# Responsive Verification

Responsive verification is mandatory for every frontend UI change.

Test the affected page at least at these viewport sizes:

* 1440x900
* 1280x800
* 1024x768
* 768x1024
* 430x932
* 390x844
* 375x667

Also identify the actual responsive breakpoints used by the project and test immediately below and above important breakpoints.

Examples:

* 639 / 640
* 767 / 768
* 1023 / 1024
* 1279 / 1280

Do not assume that testing only standard device sizes is sufficient.

---

# Responsive Audit Checklist

At every relevant viewport, verify:

* no unintended horizontal overflow
* no clipped or hidden content
* correct layout stacking
* correct spacing
* correct alignment
* correct text wrapping
* correct truncation behavior
* readable typography
* correct image sizing and aspect ratios
* correct button sizes
* usable touch targets
* correct navigation behavior
* correct dropdown positioning
* correct modal and drawer behavior
* correct tables and wide-content behavior
* correct sticky and fixed positioning
* no overlapping elements
* no broken grids or flex layouts
* no unexpected layout jumps around breakpoints

Also check whether:

```javascript
document.documentElement.scrollWidth > window.innerWidth
```

If true, find the element causing the horizontal overflow and fix the root cause.

---

# Interaction Testing

Do not judge responsiveness from a static screenshot alone.

Interact with the affected page.

Where applicable, test:

* opening and closing navigation
* dropdowns
* modals
* drawers
* tabs
* forms
* validation
* buttons
* pagination
* filters
* search
* scrolling
* sticky elements
* dynamic content
* loading states
* error states
* empty states

Verify that the UI remains correct after interaction.

---

# Content Stress Testing

Do not test only ideal content.

Where relevant, verify the UI with:

* short text
* normal text
* long text
* very long text
* long unbroken strings
* large numbers
* long prices
* missing optional data
* many list items
* zero list items

Do not allow realistic content to break the layout.

---

# RTL and LTR Verification

If the project supports RTL and LTR languages, test both directions independently.

At minimum, verify Arabic and English when supported.

Do not assume that a layout working in LTR also works in RTL.

Pay special attention to:

* alignment
* flex and grid ordering
* margins and padding
* directional icons
* arrows
* breadcrumbs
* dropdown alignment
* form controls
* input icons
* tables
* navigation
* text wrapping
* mixed text and numbers

Fix directional layout issues using proper logical CSS properties where appropriate rather than adding fragile language-specific overrides.

---

# Browser Debugging

During browser verification, inspect:

* Console
* Network
* DOM
* rendered styles
* API responses
* loading behavior
* runtime errors
* hydration errors
* failed resources
* failed API requests

Do not ignore console errors simply because the visible UI appears functional.

When an API request fails, investigate the actual cause rather than adding frontend workarounds.

---

# Root-Cause Fixing

Always fix the root cause.

Do not solve responsive or UI problems by accumulating arbitrary one-off CSS patches unless they are genuinely required.

Avoid:

* unnecessary magic numbers
* excessive viewport-specific overrides
* duplicated styles
* fragile absolute positioning
* arbitrary z-index escalation
* hiding broken elements instead of fixing them
* adding `overflow: hidden` only to conceal layout bugs

Prefer:

* correct layout structure
* flexible sizing
* CSS Grid or Flexbox where appropriate
* logical CSS properties
* reusable responsive rules
* project design-system tokens
* existing project breakpoints and conventions

Respect the existing architecture and design system.

---

# Regression Protection

When a browser issue reveals a meaningful regression risk, add or improve an automated regression test where practical.

Prefer the following workflow:

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
Run relevant test suite
↓
Final browser verification
```

For important user journeys, prefer browser automation such as Playwright if the project already uses it or if adding the test is justified.

---

# Scope of Verification

Focus browser verification on the affected feature and its directly related flows.

However, when a change affects shared components such as:

* navigation
* layout
* header
* footer
* modal system
* forms
* buttons
* design tokens
* responsive utilities
* authentication
* API client
* localization
* shared state

perform a broader regression check across representative pages that use the shared component.

---

# Completion Standard

Never report a frontend task as complete only because:

* the build passes
* lint passes
* static analysis passes
* unit tests pass
* the component renders once
* one viewport works
* desktop works
* mobile works at only one width

A frontend task is complete only when the affected functionality has been verified in the real browser across relevant states, interactions, viewport sizes, and supported layout directions.

If browser access is unavailable, explicitly state that browser verification could not be performed and do not claim that the UI was fully verified.
