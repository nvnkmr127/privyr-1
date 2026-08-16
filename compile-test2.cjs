const fs = require('fs');
const code = fs.readFileSync('packages/Webkul/Admin/src/Resources/views/lead_capture/wizard.blade.php', 'utf-8');
const startIdx = code.indexOf('<script type="text/x-template" id="v-lead-capture-integrations-template">') + 73;
const endIdx = code.indexOf('</script>', startIdx);
let template = code.substring(startIdx, endIdx);
console.log(template.substring(0, 50));
