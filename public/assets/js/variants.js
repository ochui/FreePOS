/**
 * Product Variants Management
 * JavaScript for managing product variants in the admin interface
 */

var VariantsManager = (function() {
    'use strict';

    var currentProductId = null;
    var variants = [];
    var attributes = [];

    /**
     * Initialize the variants manager
     */
    function init(productId) {
        currentProductId = productId;
        loadAttributes();
        loadVariants();
    }

    /**
     * Load all attributes
     */
    function loadAttributes() {
        $.ajax({
            url: '/api/variants/attributes',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.data) {
                    attributes = response.data;
                    renderAttributeSelector();
                }
            },
            error: function() {
                showError('Failed to load attributes');
            }
        });
    }

    /**
     * Load variants for current product
     */
    function loadVariants() {
        if (!currentProductId) return;

        $.ajax({
            url: '/api/variants/get',
            method: 'POST',
            data: JSON.stringify({ product_id: currentProductId }),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.data) {
                    variants = response.data;
                    renderVariantsTable();
                }
            },
            error: function() {
                showError('Failed to load variants');
            }
        });
    }

    /**
     * Render attribute selector for variant generation
     */
    function renderAttributeSelector() {
        var html = '<div class="variant-attribute-selector">';
        html += '<h4>Generate Variants from Attributes</h4>';
        html += '<div class="form-group">';
        html += '<label>Select Attributes:</label>';
        html += '<select id="variant-attributes" class="form-control" multiple>';
        
        attributes.forEach(function(attr) {
            html += '<option value="' + attr.id + '">' + attr.name + '</option>';
        });
        
        html += '</select>';
        html += '</div>';
        html += '<button class="btn btn-primary" onclick="VariantsManager.generateVariants()">Generate Variants</button>';
        html += '</div>';

        $('#variant-generator').html(html);
    }

    /**
     * Render variants table
     */
    function renderVariantsTable() {
        var html = '<div class="variants-table">';
        html += '<h4>Product Variants</h4>';
        html += '<table class="table table-striped">';
        html += '<thead><tr>';
        html += '<th>SKU</th>';
        html += '<th>Name Suffix</th>';
        html += '<th>Attributes</th>';
        html += '<th>Price</th>';
        html += '<th>Cost</th>';
        html += '<th>Stock</th>';
        html += '<th>Active</th>';
        html += '<th>Actions</th>';
        html += '</tr></thead><tbody>';

        if (variants.length === 0) {
            html += '<tr><td colspan="8" class="text-center">No variants found. Create a default variant or generate from attributes.</td></tr>';
        } else {
            variants.forEach(function(variant) {
                html += '<tr data-variant-id="' + variant.id + '">';
                html += '<td>' + escapeHtml(variant.sku) + (variant.is_default == 1 ? ' <span class="badge badge-info">Default</span>' : '') + '</td>';
                html += '<td>' + escapeHtml(variant.name_suffix || '') + '</td>';
                
                // Attributes
                var attrStr = '';
                if (variant.attributes && variant.attributes.length > 0) {
                    attrStr = variant.attributes.map(function(a) { return a.value; }).join(' / ');
                }
                html += '<td>' + escapeHtml(attrStr) + '</td>';
                
                html += '<td><input type="number" class="form-control input-sm variant-price" value="' + variant.price + '" step="0.01" min="0" data-field="price"></td>';
                html += '<td><input type="number" class="form-control input-sm variant-cost" value="' + variant.cost + '" step="0.01" min="0" data-field="cost"></td>';
                
                // Stock summary
                var totalStock = 0;
                if (variant.stock && variant.stock.length > 0) {
                    variant.stock.forEach(function(s) { totalStock += parseInt(s.stocklevel); });
                }
                html += '<td>' + totalStock + '</td>';
                
                html += '<td><input type="checkbox" class="variant-active" ' + (variant.is_active == 1 ? 'checked' : '') + ' data-field="is_active"></td>';
                html += '<td>';
                html += '<button class="btn btn-sm btn-success variant-save" onclick="VariantsManager.saveVariant(' + variant.id + ')">Save</button> ';
                if (variant.is_default != 1) {
                    html += '<button class="btn btn-sm btn-danger variant-delete" onclick="VariantsManager.deleteVariant(' + variant.id + ')">Delete</button>';
                }
                html += '</td>';
                html += '</tr>';
            });
        }

        html += '</tbody></table>';
        html += '<button class="btn btn-primary" onclick="VariantsManager.showAddVariantForm()">Add Variant Manually</button>';
        html += '</div>';

        $('#variants-list').html(html);
    }

    /**
     * Generate variants from selected attributes
     */
    function generateVariants() {
        var selectedAttrs = $('#variant-attributes').val();
        
        if (!selectedAttrs || selectedAttrs.length === 0) {
            showError('Please select at least one attribute');
            return;
        }

        $.ajax({
            url: '/api/variants/generate',
            method: 'POST',
            data: JSON.stringify({
                product_id: currentProductId,
                attribute_ids: selectedAttrs.map(function(id) { return parseInt(id); })
            }),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.data) {
                    showSuccess('Generated ' + response.data.created_count + ' variants');
                    loadVariants();
                } else if (response.error) {
                    showError(response.error);
                }
            },
            error: function() {
                showError('Failed to generate variants');
            }
        });
    }

    /**
     * Save variant changes
     */
    function saveVariant(variantId) {
        var row = $('tr[data-variant-id="' + variantId + '"]');
        var updateData = {
            id: variantId,
            price: parseFloat(row.find('.variant-price').val()),
            cost: parseFloat(row.find('.variant-cost').val()),
            is_active: row.find('.variant-active').is(':checked') ? 1 : 0
        };

        $.ajax({
            url: '/api/variants/update',
            method: 'POST',
            data: JSON.stringify(updateData),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.data) {
                    showSuccess('Variant updated successfully');
                } else if (response.error) {
                    showError(response.error);
                }
            },
            error: function() {
                showError('Failed to update variant');
            }
        });
    }

    /**
     * Delete a variant
     */
    function deleteVariant(variantId) {
        if (!confirm('Are you sure you want to delete this variant?')) {
            return;
        }

        $.ajax({
            url: '/api/variants/delete',
            method: 'POST',
            data: JSON.stringify({ id: variantId }),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.data) {
                    showSuccess('Variant deleted successfully');
                    loadVariants();
                } else if (response.error) {
                    showError(response.error);
                }
            },
            error: function() {
                showError('Failed to delete variant');
            }
        });
    }

    /**
     * Show add variant form
     */
    function showAddVariantForm() {
        // This would open a modal or inline form to add a variant manually
        // Implementation depends on the UI framework being used
        alert('Manual variant creation form - to be implemented based on UI framework');
    }

    /**
     * Utility functions
     */
    function escapeHtml(text) {
        if (!text) return '';
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function showError(message) {
        // Use your preferred notification method
        alert('Error: ' + message);
    }

    function showSuccess(message) {
        // Use your preferred notification method
        alert('Success: ' + message);
    }

    // Public API
    return {
        init: init,
        generateVariants: generateVariants,
        saveVariant: saveVariant,
        deleteVariant: deleteVariant,
        showAddVariantForm: showAddVariantForm,
        reload: loadVariants
    };
})();
