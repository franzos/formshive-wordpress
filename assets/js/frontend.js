/**
 * Frontend JavaScript for Formshive plugin - includes the existing embed script
 */

// Shared utility functions
function loadAltchaIfNeeded(html) {
  if (html.includes('altcha-widget')) {
    if (!document.querySelector('script[src*="altcha.min.js"]')) {
      const altchaScript = document.createElement('script');
      altchaScript.src = 'https://cdn.jsdelivr.net/npm/altcha/dist/altcha.min.js';
      altchaScript.type = 'module';
      altchaScript.async = true;
      altchaScript.defer = true;
      document.head.appendChild(altchaScript);
    }
  }
}

function showErrorMessage(container, message) {
  container.innerHTML = `
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
    framework = 'formshive',
    apiEndpoint = 'https://api.formshive.com/v1',
    title = null,
    rustyFormsDiv = null
  } = options;

  const targetDiv = rustyFormsDiv || document.querySelector('div#formshive');

  if (!targetDiv) {
    return;
  }

  if (!formId) {
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
  const supportedFrameworks = ['formshive', 'bootstrap', 'bulma'];
  if (framework && !supportedFrameworks.includes(framework)) {
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
      showErrorMessage(targetDiv, `Failed to load the form. Error: ${error.message}`);
    });
}

// WordPress plugin integration
document.addEventListener('DOMContentLoaded', function () {
  // Handle embedded forms
  const embeddedForms = document.querySelectorAll('.formshive-embed');
  
  embeddedForms.forEach(function(container) {
    const formId = container.getAttribute('data-form-id');
    const framework = container.getAttribute('data-framework') || 'formshive';
    
    if (formId && typeof formshive_config !== 'undefined') {
      loadFormshiveForm({
        formId: formId,
        framework: framework,
        apiEndpoint: formshive_config.api_endpoint,
        rustyFormsDiv: container
      });
    }
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