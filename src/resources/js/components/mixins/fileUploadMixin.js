export const fileUploadMixin = {
    methods: {
        uploadPublicFileToVueCRUDController: function(actionUrl, file, actionName) {
            return new Promise((resolve, reject) =>  {
                let fileReader = new FileReader();
                fileReader.readAsDataURL(file);
                fileReader.onloadend = (readerEvent) => {
                    let uploadData = {
                        "fileName": file.name,
                        "fileData": readerEvent.target.result,
                        "fileType": file.type,
                        "action": actionName
                    }
                    window.axios.post(actionUrl, uploadData)
                        .then((response) => {
                            return resolve(response);
                        })
                        .catch((error) => {
                            return reject(error);
                        })
                }

            })
        },
        removeUploadedPublicFile: function(actionUrl, fileUrl, actionName) {
            return new Promise((resolve, reject) =>  {
                let uploadData = {
                    "action": actionName,
                    "url": fileUrl
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