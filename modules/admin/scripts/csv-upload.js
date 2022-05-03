class CSVUploader {

    constructor() {
        this.form = document.getElementById("csv-submit");
        this.fileInput = document.getElementById("csv-file");
        if ( this.form && this.fileInput ) {
            this.form.onsubmit = this.onSend;
        }
    }

    onSend = async event => {
        event.preventDefault();

        const file = this.fileInput.files[0];
        const filename = file.name;
        const url = this.form.getAttribute("data-url");
        let data, body;

        await file.text().then( text => {
            data = text.split("\n");
            body = {
                form_data: data,
                table_name: filename
            }
        }).then( _ => {
            fetch(
                url,
                {
                    method: 'POST',
                    cache: 'no-cache',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(body)
                }
            ).then( response => {
                if (!response.ok) {
                    console.error(response.text());
                } else {
                    location.reload();
                }
            });
        }).catch(error => {
            console.error(error);
        });

    }

}

const uploader = new CSVUploader();