new class {
    constructor() {
        this.init();
    }

    init() {
        this.init_parameters();
        this.init_triggers();
    }

    init_triggers() {
        this.trigger_add_row();
        this.trigger_remove_row();
    }

    init_parameters() {
        this.container_error = $('.p-errors');
        this.btn_add = $('#add-row');
        this.btn_remove = $('.rm-btn');
    }


    trigger_add_row() {
        var app = this;
        this.btn_add.off();
        this.btn_add.click(function (e) {
            e.preventDefault();
            var id = Date.now() / 1000 | 0;
            var html = `
            <tr id="local-` + id + `">
                <td>
                    
                        <div class="form-group" data-toggle="tooltip" data-tippy-content="` + KeyText + `">
                            <label for="">`+KeyLabel+`</label>
                            <input type="text" class="form-control" name="new[` + id + `][key]" value="">
                            <span class="material-input"></span>
                        </div>
                   
                </td>
                <td>
                        <div class="form-group" data-toggle="tooltip" data-tippy-content="` + ValText + `">
                            <label for="">`+ValLabel+`</label>
                            <input type="text" class="form-control" name="new[` + id + `][val]" value="">
                            <span class="material-input"></span>
                        </div>                                                  
                </td>
                <td rowspan="2">
                    <div class="rm-btn">
                        <button class="rm-btn btn-round btn btn-danger" data-mode="local" data-toggle="tooltip" data-tippy-content="` + DeleteText + `" data-id="` + id + `">
                            <span class="material-icons">
clear
</span>
                        <div class="ripple-container"></div></button>
                    </div>
                </td>
                
                
                <tr id="local2-`+id+`">
                    <td colspan="2">
                        <label for="">` + DescriptionLabel + `</label>
                        <span class="bmd-form-group is-filled">
                        <div class="form-group">
                        <input type="text" class="form-control" name="keys[` + id + `][description]" value="">
                        <span class="material-input"></span></div></span>                                                            
                    </td>
                </tr>
            </tr>
            `;
            $('#data-table').append(html);
            tippy('[data-toggle="tooltip"]');
            app.init();
        });
    }

    trigger_remove_row() {
        var app = this;
        this.btn_remove.off();
        this.btn_remove.click(function (e) {
            e.preventDefault();
            var mode = $(this).attr('data-mode');
            var id = $(this).attr('data-id');
            switch (mode) {
                case 'db':
                    app.ajax(
                        RemoveUrl,
                        {
                            id: id
                        },
                    );
                    break;
                case 'local':
                    $('#local-' + id).remove();
                    $('#local2-' + id).remove();
                    break;
            }
        });
    }

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
        `);
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
                });
                break;
            case 'removed':
                $('#db-' + response.data).remove();
                $('#db2-' + response.data).remove();
                break;
        }
    }

}
