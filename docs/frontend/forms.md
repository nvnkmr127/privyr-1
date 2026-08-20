# Frontend Forms

Krayin's forms are a mix of standard Blade HTML and Vue.js reactivity.

## Dynamic EAV Forms
Because Leads, Persons, and Organizations rely on custom Attributes, their forms cannot be hardcoded in HTML.
Instead, the view loops over `$attributes` and renders the corresponding input field.

## Validation (VeeValidate)
Vue components use plugins (like VeeValidate) to enforce frontend rules:
- `v-validate="'required|email'"`
- Errors are displayed below the input reactively without waiting for a server response.

## Submission
Forms are either submitted via standard POST/PUT requests (which cause a page reload and redirect back with flashed errors) or via Axios (for Modals and Kanbans).
