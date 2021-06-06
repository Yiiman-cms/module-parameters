function insertTextAtCursor(text) {
    var doc = editorMirror.getDoc();
    var cursor = doc.getCursor();
    doc.replaceRange(text, cursor);
}

var pApp = new class {
    fields;
    modal;
    translates;
    values = {};
    out = '';
    name;

    start() {
        this.init();

    }

    init() {
        this.init_translates();
        this.init_modal();
        this.init_fields();
        // this.init_parameters();
        this.init_triggers();
    }

    init_translates() {
        this.translates = ptranslates;
    }

    init_fields() {
        var html = '<div class="row">';
        $.each(this.fields, function (key, item) {
            console.log(item);
            switch (item.mode) {
                case 'text':
                    html += `
                    <div class="col-md-6"><div class="form-group field-widget-title required bmd-form-group is-filled has-success">
                        <label class="control-label bmd-label-static" for="pp` + key + `">` + item.label + `</label>
                        <input type="text" key="` + key + `"  class="form-control" id="pp` + key + `" value=""  aria-required="true" aria-invalid="false">

                        <div class="help-block"></div>
                            <span class="material-input"></span>
                        </div>
                    </div>
                    `;
                    break;
                case 'checkbox':
                    html += `
                    <div class="col-md-6"><div class="form-group field-widget-title required bmd-form-group is-filled has-success">
                        <label class="control-label bmd-label-static" for="pp` + key + `">` + item.label + `</label>
                        <input type="checkbox" key="` + key + `"  class="form-control" id="pp` + key + `" value=""  aria-required="true" aria-invalid="false">

                        <div class="help-block"></div>
                            <span class="material-input"></span>
                        </div>
                    </div>
                    `;
                    break;

                case 'number':
                    html += `
                    <div class="col-md-6"><div class="form-group field-widget-title required bmd-form-group is-filled has-success">
                        <label class="control-label bmd-label-static" for="pp` + key + `">` + item.label + `</label>
                        <input type="number" key="` + key + `"  class="form-control" id="pp` + key + `" value=""  aria-required="true" aria-invalid="false">

                        <div class="help-block"></div>
                            <span class="material-input"></span>
                        </div>
                    </div>
                    `;
                    break;


            }
        })

        // < Bind To Modal >
        {
            html += '</div>';
            $('#purl-modal .modal-body').html(html);
            this.open_modal();
        }
        // </ Bind To Modal >

    }


    open_modal() {
        this.modal.modal('show');
    }

    close_modal() {
        this.modal.modal('hide');
    }


    init_modal() {
        this.modal = $('#purl-modal');
        if (this.modal.length === 0) {
            $('body').prepend(
                `
                    <div class="modal" tabindex="-1" role="dialog" id="purl-modal">
                          <div class="modal-dialog" role="document">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title">` + this.translates.header + `</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                                <div class="row">
                                    <div class="col-md-8">
                                        ` + this.translates.help + `
                                    </div>
                                </div>
                              </div>
                              <div class="modal-body">
                                
                              </div>
                              <div class="row">
                                  <div class="col-md-12">
                                        <span id="pOut"></span>
                                  </div>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-primary" id="ppcopy">` + this.translates.save + `</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">` + this.translates.close + `</button>
                              </div>
                            </div>
                          </div>
</div>
                    `
            );
            this.modal = $('#purl-modal');

        }

        $('#pOut').empty();
    }

    init_triggers() {
        var app = this;

        $.each(this.fields, function (key, item) {

            switch (item.mode) {

                case 'text':

                    $('#pp' + key).off();
                    $('#pp' + key).keyup(function (e) {
                        // if ($(this).attr('key') in app.values) {
                        eval('app.values.' + $(this).attr('key') + '="' + $(this).val() + '";')

                        // } else {
                        //     app.values.push('key', $(this).val());
                        // }

                        app.buildParameter();
                    });
                    break;
                case 'checkbox':
                    $('#pp' + key).off();
                    $('#pp' + key).click(function (e) {
                        console.log('keyup');
                        if ($(this).prop('checked')) {
                            console.log('has');
                            console.log($(this).attr('key'));
                            eval('app.values.' + $(this).attr('key') + '="' + $(this).attr('key') + '";')
                        }
                        app.buildParameter();
                    });
                    break;
            }
        });


        // < Code >
        {
            $('#ppcopy').click(function (e) {

                insertTextAtCursor($('#pOut').text());

                // $('#pOut').selectText();
                // document.execCommand("copy");
                // document.getSelection().removeAllRanges();
                // alert('کد کپی شد، حالا باید آن را در متن خود قرار دهید');
                app.close_modal();
            });
        }
        // </ Code >
    }


    buildParameter() {
        var app = this;
        app.out = '{{{'+this.name ;

        $.each(this.values, function (key, item) {
            app.out += ' ['  + item + '] ';
        });
        app.out += '}}}';
        $('#pOut').text(app.out);
    }

    //
    // init_parameters() {
    //     this.container_error = $('#');
    // }


    ajax(url, data, errorCallBack = function () {
    }) {
        var app = this;
        $.ajax({
            url: url,
            method: 'post',
            data: data,
            success: function (response) {
                app.checkResponse(response);
            },
            error: errorCallBack
        });

    }


    hideError() {
        this.container_error.empty();
    }

    addError(message) {
        this.container_error.append(`
            <li>` + message + `</li>
            `)
    }


    checkResponse(response) {
        var app = this;
        switch (response.status) {
            case 'error':
                this.addError(response.message);
                break;
            case 'errors':
                $.each(response.messages, function (index, value) {
                    app.addError(value);
                })
                break;
        }
    }

}

jQuery.fn.selectText = function () {
    this.find('input').each(function () {
        if ($(this).prev().length == 0 || !$(this).prev().hasClass('p_copy')) {
            $('<p class="p_copy" style="position: absolute; z-index: -1;"></p>').insertBefore($(this));
        }
        $(this).prev().html($(this).val());
    });
    var doc = document;
    var element = this[0];

    if (doc.body.createTextRange) {
        var range = document.body.createTextRange();
        range.moveToElementText(element);
        range.select();
    } else if (window.getSelection) {
        var selection = window.getSelection();
        var range = document.createRange();
        range.selectNodeContents(element);
        selection.removeAllRanges();
        selection.addRange(range);
    }
};
$('.p-parameter').click(function () {
    if ($(this).attr('function') === 'false') {
        insertTextAtCursor('{{' + $(this).text() + '}}');
        // let key = '{{' + $(this).text() + '}}';
        // let origin = $(this).text();
        // var text_val=eval(this);
        //        text_val.focus();
        //        text_val.select();
        //        r = text_val.createTextRange();
        //        if (!r.execCommand) return; // feature detection
        //        r.execCommand('copy');
        //
        // $(this).text(key);
        // $(this).selectText();
        // document.execCommand("copy");
        // document.getSelection().removeAllRanges();
        // $(this).text(origin);
    } else {
        let fields = $(this).attr('fields');
        pApp.fields = eval(fields);
        pApp.name = $(this).text();
        pApp.values = {};
        pApp.start();
    }
});


