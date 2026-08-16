const { compile } = require('@vue/compiler-dom');
const fs = require('fs');

const code = fs.readFileSync('packages/Webkul/Admin/src/Resources/views/lead_capture/wizard.blade.php', 'utf-8');

// Extract just the template part
const startIdx = code.indexOf('<script type="text/x-template" id="v-lead-capture-integrations-template">') + 73;
const endIdx = code.indexOf('</script>', startIdx);
let template = code.substring(startIdx, endIdx);

// Remove blade directives
template = template.replace(/@{{/g, '{{');

try {
    compile(template, {
        onError(err) {
            console.error(err);
        }
    });
} catch (e) {
    console.error('Crash:', e);
}
