var Kora = Kora || {};
Kora.Records = Kora.Records || {};

Kora.Records.Validate = function() {
  var errorList = [];

    function initializeValidationModal() {
        Kora.Modal.initialize();

        var uniquePages = Array.from(new Set(errorList));

        $('div.error-pages p').remove();
        $('.error-count-js').text(errorList.length);
        errorList = [];

        uniquePages.forEach(function(page, uniquePages){
          pageLink = page;
          pageNum = $('.content-sections-scroll').children('a[href="'+pageLink+'"]').index() + 1;
          page = page.substring(1);
          $('div.error-pages').append('<p><a href="'+pageLink+'" class="validation-errorpage-link">Page '+pageNum+' - '+page+'</a></p>');
        });

        Kora.Modal.open($('.record-validation-modal-js'));
    }

    function validationModal() {
      $('.error-pages').on('click', 'a', function(e){
        e.preventDefault();

        var $this = $(this).attr('href');
        Kora.Modal.close($('.record-validation-modal-js'));

        $('.content-sections-scroll').find('a[href="'+ $this +'"]').trigger('click');
      });
    }

    function initializeRecordValidation() {
        $('.record-validate-js').click(function(e) {
            var $this = $(this);

            e.preventDefault();

            values = {};
            //We need to make sure all CKEDITOR data is actually in the form to validate it
            for(var instanceName in CKEDITOR.instances){
                CKEDITOR.instances[instanceName].updateElement();
            }
            $.each($('.record-form').serializeArray(), function(i, field) {
                if(field.name in values)
                    if(Array.isArray(values[field.name]))
                        values[field.name].push(field.value);
                    else
                        values[field.name] = [values[field.name], field.value];
                else
                    values[field.name] = field.value;
            });
            values['_method'] = 'POST';
            values['tmpFileDir'] = tmpFileDir;

            $.ajax({
                url: validationUrl,
                method: 'POST',
                data: values,
                success: function(err) {
                    $('.error-message').text('');
                    $('.text-input, .text-area, .cke, .chosen-container, .new-list-option-card-js').removeClass('error');

                    console.warn(err);

                    if(err.errors.length==0) {
                        $('.record-form').submit();
                    } else {
                        $.each(err.errors, function(fieldName, error) {
                            var $field = $('#'+fieldName);
                            var $page = $field.parents('section').attr('id');

                            if($page === undefined) {
                              $field = $('[name="' + fieldName + '"]');
                              $page = $field.parents('section').attr('id');
                            }
                            if($page === undefined) {
                              $field = $('[name="' + fieldName + '[]"]');
                              $page = $field.parents('section').attr('id');
                            }

                            $field.addClass('error');
                            $field.siblings('.error-message').text(error);

                            if($field.siblings('.chosen-container') !== false)
                                $field.siblings('.chosen-container').addClass('error');

                            errorList.push($page);
                        });
                        
                        initializeValidationModal();
                    }
                },
                error: function(err) {
                    console.log(err);
                }
            });
        });
    }

    validationModal();
    initializeRecordValidation();
    Kora.Records.Modal();
}
