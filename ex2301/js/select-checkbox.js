(function ($) {
    $.fn.selectCheckbox = function () {
        return this.each(function () {

            let $select = $(this);
            let selectIndex = $('select').index($select); // Obtém a posição do select no DOM
            let title = $select.attr('title') || 'Selecionar';
            let fullEnabled = $select.data('sc-full') || false;
            let fullMobileEnabled = $select.data('sc-full-mobile') || false;
            let searchEnabled = $select.data('sc-search') || false;
            let isMultiple = $select.prop('multiple'); // Verifica se é múltiplo

            if (searchEnabled === "auto") {
                searchEnabled = $select.children("option").length > 30;
            }

            // Na dúvida se precisa
            // if (isMultiple) {
            //     if (!$select.attr('name').endsWith('[]')) {
            //         $select.attr('name', $select.attr('name') + '[]');
            //     }
            // }

            // Remove instâncias anteriores para evitar duplicação
            $select.next('.select-checkbox').remove();
            $select.hide();

            let $wrapper = $('<div class="select-checkbox-wrapper"></div>');
            let $customSelect = $('<div class="select-checkbox"></div>');
            let $button = $('<button type="button" class="btn"><span>' + title + '</span> <i class="fa fa-chevron-down"></i></button>');
            let $dropdown = $('<div class="select-checkbox-dropdown"></div>');
            let $dropdownTitle = $('<div class="fw-bold mb-2">' + title + '</div>');
            let $checkboxContainer = $('<div class="checkboxes custom-scroll scroll-y"></div>');
            let $searchBox = $('<div class="search mb-2">' +
                '<div class="d-flex justify-content-between rounded ps-2 bg-white border brd-color-body">' +
                '<input type="text" class="bg-transparent border-0 w-100" autocomplete="off" placeholder="Filtrar... ">' +
                '<span class="p-2"><i class="fa fa-search"></i></span>' +
                '</div>' +
                '</div>');
            let $actionButtons = $('<div class="action d-flex justify-content-between pt-3">' +
                '<a class="clean btn btn-sm border cursor-pointer">Limpar</a>' +
                '<a class="apply btn btn-sm border cursor-pointer">Aplicar</a>' +
                '</div>');

            if (searchEnabled) {
                $dropdown.append($searchBox);
                $searchBox.find('input').on('input', function () {
                    let searchTerm = $(this).val().toLowerCase();
                    $checkboxContainer.find('.form-check').each(function () {
                        const text = $(this).find('label').text().toLowerCase();
                        $(this).toggle(text.includes(searchTerm));
                    });
                });
            }

            let $options = $select.children();
            if ($options.length === 1 && $options.first().val() === '') {
                $checkboxContainer.append('<div class="text-muted fst-italic">Nenhuma opção disponível</div>');
            } else {
                $select.children().each(function () {
                    if (this.tagName === 'OPTGROUP') {
                        let groupTitle = $(this).attr('label');
                        $checkboxContainer.append('<div class="optgroup-title fw-bold mt-2">' + groupTitle + '</div>');
                        $(this).children('option').each(function () {
                            appendCheckbox($(this),selectIndex);
                        });
                        // $checkboxContainer.append('<div class="border-bottom my-2"></div>');
                    } else if (this.tagName === 'OPTION') {
                        appendCheckbox($(this),selectIndex);
                    }
                });
            }

            function appendCheckbox($option, index) {

                let value = $option.attr('value');
                let label = $option.text();
                let isSelected = $option.attr('selected');
                let checkboxId = 'check-' + index + '-' + value;
                let hasInlineClass = $option.hasClass('inline');

                if (value === '') return;

                let $checkbox = $('<div class="form-check' + (hasInlineClass ? ' inline' : '') + '">' +
                    '<input class="form-check-input" type="checkbox" value="' + value + '" id="' + checkboxId + '"' + (isSelected ? ' checked' : '') + '>' +
                    '<label class="form-check-label" for="' + checkboxId + '">' + label + '</label>' +
                    '</div>');

                $checkbox.find('input').on('change', function () {

                    if (!isMultiple) {
                        // Se for um select normal, desmarca os outros checkboxes
                        $checkboxContainer.find('input').prop('checked', false);
                        $(this).prop('checked', true);
                    }

                    // Atualiza o select original
                    $select.find('option').prop('selected', false);
                    $checkboxContainer.find('input:checked').each(function () {
                        $select.find('option[value="' + $(this).val() + '"]').prop('selected', true);
                    });

                    updateButtonLabel();
                    $select.trigger("change");

                });

                $checkboxContainer.append($checkbox);

            }

            function updateButtonLabel() {
                let selectedCount = $checkboxContainer.find('input:checked').length;
                let selectedText = selectedCount > 0 ? ' (' + selectedCount + ')' : '';
                $button.html('<span>' + title + '</span>' + selectedText + ' <i class="fa fa-chevron-down"></i>');
            }

            $dropdown.append($checkboxContainer).append($actionButtons);

            // Abre em pop up
            if (fullEnabled || (fullMobileEnabled && window.innerWidth <= 768)) {
                $wrapper.append($dropdown);
                $dropdown.prepend($dropdownTitle);
                $dropdown.addClass('full');
            } else {
                $wrapper = $dropdown;
            }

            $customSelect.append($button).append($wrapper);

            $select.after($customSelect);

            $button.on('click', function () {
                $(".select-checkbox-dropdown").removeClass("show");
                $dropdown.toggleClass('show');
                if ($wrapper !== $dropdown) $wrapper.toggleClass('show');
            });

            $actionButtons.find('.clean').on('click', function () {
                $checkboxContainer.find('input').prop('checked', false);
                updateButtonLabel();
                $select.find('option').prop('selected', false);
                $select.trigger("change");
            });

            $actionButtons.find('.apply').on('click', function () {
                $dropdown.removeClass('show');
                if ($wrapper !== $dropdown) $wrapper.removeClass('show');
            });

            // Fecha clicando fora
            $(document).on("click", function (event) {
                if (!$(event.target).closest(".select-checkbox").length) {
                    $(".select-checkbox-dropdown").removeClass("show");
                    $(".select-checkbox-wrapper").removeClass("show");
                }
            });

            // Fecha clicando no wrapper
            $wrapper.on('click', function (event) {
                if (!$(event.target).closest('.select-checkbox-dropdown').length) {
                    $dropdown.removeClass('show');
                    if ($wrapper !== $dropdown) $wrapper.removeClass('show');
                }
            });

            updateButtonLabel();

        });
    };
})(jQuery);
