/**
 * JavaScript for form editing course completed count condition.
 *
 * @module moodle-availability_courseenrollmentcount-form
 */

M.availability_courseenrollmentcount = M.availability_courseenrollmentcount || {};

// Class M.availability_courseenrollmentcount.form @extends M.core_availability.plugin.
M.availability_courseenrollmentcount.form = Y.Object(M.core_availability.plugin);

/**
 * Initialises this plugin.
 *
 * @method initInner
 * @param {boolean} completed Is completed or not
 */
M.availability_courseenrollmentcount.form.initInner = function() {
};

M.availability_courseenrollmentcount.form.getNode = function(json) {
    // Create HTML structure.
    var tit = M.util.get_string('title', 'availability_courseenrollmentcount');
    var html = '<label class="form-group"><span class="p-r-1">' + tit + '</span>';
    html += '<span class="availability_courseenrollmentcount"><select class="custom-select" name="cpmop" title=' + tit + '>';
    html += '<option value="choose">' + M.util.get_string('choosedots', 'moodle') + '</option>';
    html += '<option value="0">=</option>';
    html += '<option value="1"><</option>';
    html += '<option value="2">></option>';
    html += '<option value="3"><=</option>';
    html += '<option value="4">>=</option>';
    html += '</select></span>';
    html += '<span class="availability_courseenrollmentcount"><input name="cnt" type="number"></span></label>';
    var node = Y.Node.create('<span class="form-inline">' + html + '</span>');

    // Set initial values (leave default 'choose' if creating afresh).
        if (json.cpmop !== undefined
            && node.one('select[name=cpmop] > option[value=' + json.cpmop + ']')) {
            node.one('select[name=cpmop]').set('value', '' + json.cpmop);
        } else if (json.cpmop === undefined) {
            node.one('select[name=cpmop]').set('value', 4);
        } else {
            node.one('select[name=cpmop]').set('value', 4);
        }
        if (json.cnt !== undefined) {
            node.one('input[name=cnt]').set('value', json.cnt);
        } else {
            node.one('input[name=cnt]').set('value', 0);
        }

    // Add event handlers (first time only).
    if (!M.availability_courseenrollmentcount.form.addedEvents) {
        M.availability_courseenrollmentcount.form.addedEvents = true;
        var root = Y.one('.availability-field');
        root.delegate('change', function() {
            // Just update the form fields.
            M.core_availability.form.update();
        }, '.availability_courseenrollmentcount select');
        root.delegate('valuechange', function() {
            // For grade values, just update the form fields.
            M.core_availability.form.update();
        }, '.availability_courseenrollmentcount input[name=cnt]');
        root.delegate('click', function() {
            // For grade values, just update the form fields.
            M.core_availability.form.update();
        }, '.availability_courseenrollmentcount input[name=cnt]');

    }

    return node;
};

// values are filled during form.update - add event listeners
M.availability_courseenrollmentcount.form.fillValue = function(value, node) {
    var selected = node.one('select[name=cpmop]').get('value');
    if (selected === 'choose') {
        value.cpmop = '';
    } else {
        value.cpmop = parseInt(selected);
    }
    value.cnt = parseInt(node.one('input[name=cnt]').get('value'));
};

M.availability_courseenrollmentcount.form.fillErrors = function(errors, node) {
    var selected = node.one('select[name=cpmop]').get('value');
    if (selected === 'choose') {
        errors.push('availability_courseenrollmentcount:operatormissing');
    }
};