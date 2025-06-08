I have developed Formshive, a form builder that allows users to create forms and collect data.
Now I want to develop a Wordpress plugin, that allows users to easily embed their forms, onto their Wordpress website.

The plugin should support multiple forms.

The flow is like this:

1. User is on a view, with the list of forms (Wordpress admin page)
2. User adds a new form, and enters the form URL `https://api.formshive.com/v1/digest/2ce22659-397b-412c-abe8-a64ce53dc4a0` (which ends with the form ID) and selects whether to embed the form or create it directly in Wordpress.
---

It should also be possible to change the API endpoint for local testing; This option should be hidden from the users.

## EMBED

I currently use the following script, to embed forms:

```js
// Shared utility functions
function loadAltchaIfNeeded(html) {
  if (html.includes('altcha-widget')) {
    if (!document.querySelector('script[src*="altcha.min.js"]')) {
      console.log('Altcha widget detected, loading script');
      const altchaScript = document.createElement('script');
      altchaScript.src = 'https://cdn.jsdelivr.net/npm/altcha/dist/altcha.min.js';
      altchaScript.type = 'module';
      altchaScript.async = true;
      altchaScript.defer = true;
      document.head.appendChild(altchaScript);
      console.log('Altcha script loaded');
    }
  } else {
    console.log('No Altcha widget detected in HTML');
  }
}

function showErrorMessage(rustyFormsDiv, message) {
  rustyFormsDiv.innerHTML = `
    <div style="border: 2px solid #f44336; padding: 16px; border-radius: 4px; background-color: #ffebee;">
      <h3 style="color: #d32f2f; margin-top: 0;">Form Loading Error</h3>
      <p>${message}</p>
    </div>
  `;
}

// Main form loading function that can be used by both embed and link scenarios
function loadFormshiveForm(options = {}) {
  const {
    formId,
    framework = 'bootstrap',
    apiEndpoint = 'https://api.formshive.com/v1',
    title = null,
    rustyFormsDiv = null
  } = options;

  const targetDiv = rustyFormsDiv || document.querySelector('div#formshive');

  if (!targetDiv) {
    console.error('Error: Could not find div with id="formshive"');
    return;
  }

  if (!formId) {
    console.error('Error: Missing form ID');
    showErrorMessage(targetDiv, 'Missing form ID. Please provide a valid form ID.');
    return;
  }

  // Update title if provided
  if (title) {
    const titleElement = document.querySelector('h1#title');
    if (titleElement) {
      titleElement.textContent = title.replace(/"/g, '');
    }
  }

  // Validate framework
  const supportedFrameworks = ['bootstrap', 'bulma'];
  if (framework && !supportedFrameworks.includes(framework)) {
    console.error(
      `Error: Unsupported framework "${framework}". Supported frameworks are: ${supportedFrameworks.join(', ')}`
    );
    showErrorMessage(
      targetDiv,
      `Unsupported framework "${framework}". Supported frameworks are: ${supportedFrameworks.join(', ')}.`
    );
    return;
  }

  fetch(`${apiEndpoint}/forms/${formId}/html?iframe=false&css_framework=${framework}`)
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
      }
      return response.text();
    })
    .then((html) => {
      const needsAltcha = html.includes('altcha-widget');

      targetDiv.outerHTML = html;
      if (needsAltcha) {
        setTimeout(() => {
          loadAltchaIfNeeded(html);
        }, 200);
      }
    })
    .catch((error) => {
      console.error('Error fetching form HTML:', error);
      showErrorMessage(targetDiv, `Failed to load the form. Error: ${error.message}`);
    });
}

// Original embed functionality for backward compatibility
document.addEventListener('DOMContentLoaded', function () {
  const rustyFormsDiv = document.querySelector('div#formshive');

  if (!rustyFormsDiv) {
    console.error('Error: Could not find div with id="formshive"');
    return;
  }

  const formId = rustyFormsDiv.getAttribute('form-id');
  let framework = rustyFormsDiv.getAttribute('framework');

  if (!framework) {
    framework = 'bootstrap'; // Default to bootstrap if no framework is specified
  }

  const isLive = true;
  let apiUrl = 'https://api.formshive.com/v1';
  if (!isLive) {
    apiUrl = 'http://localhost:8001/v1';
  }

  loadFormshiveForm({
    formId,
    framework,
    apiEndpoint: apiUrl,
    rustyFormsDiv
  });
});

// Export functions for use in other scripts
if (typeof window !== 'undefined') {
  window.FormshiveUtils = {
    loadFormshiveForm,
    loadAltchaIfNeeded,
    showErrorMessage
  };
}
```