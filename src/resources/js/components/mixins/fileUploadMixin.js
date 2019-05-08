export const fileUploadMixin = {
    methods: {
        uploadPublicFileToVueCRUDController: function(actionUrl, file, actionName, mode) {
            return new Promise((resolve, reject) =>  {
                mode = typeof(mode) == 'undefined' ? 'url' : mode;
                try {
                    let fileReader = new FileReader();
                    fileReader.readAsDataURL(file);
                    fileReader.onloadend = (readerEvent) => {
                        let uploadData = {
                            "fileName": file.name,
                            "fileData": readerEvent.target.result,
                            "fileType": file.type,
                            "action": actionName,
                            "mode": mode,
                        }
                        window.axios.post(actionUrl, uploadData)
                            .then((response) => {
                                return resolve(response);
                            })
                            .catch((error) => {
                                return reject(error);
                            })
                    }
                } catch (error) {
                    alert(error.message);
                }

            })
        },
        removeUploadedPublicFile: function(actionUrl, file, actionName, mode) {
            return new Promise((resolve, reject) =>  {
                mode = typeof(mode) == 'undefined' ? 'url' : mode;
                let uploadData = {
                    "action": actionName,
                    "file": file,
                    "mode": mode,
                };
                window.axios.post(actionUrl, uploadData)
                    .then((response) => {
                        return resolve(response);
                    })
                    .catch((error) => {
                        return reject(error);
                    })
            })
        }
    }
}