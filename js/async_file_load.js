/*
Ассинхронная загрузка файлов
*/


/*
document.getElementById("schemForm").addEventListener("click", function(e) {
	if (e.target && e.target.classList.contains("uploadBtn")) {
		var fileInputGroup = e.target.parentNode.parentNode;
		var fileInput = fileInputGroup.querySelector(".fileInput");        
		var fileList = fileInputGroup.parentNode.querySelector(".fileList");
		//console.log(fileList);


		var files = fileInput.files;
		// Получаем baseName and itemId из data атрибутов input
		var fieldId = fileInput.getAttribute("data-fieldid");
		var baseName = fileInput.getAttribute("data-basename");
		var itemId = fileInput.getAttribute("data-itemid");

		for (var i = 0; i < files.length; i++) {
			uploadFile(files[i], fileList, fieldId, baseName, itemId);
		}

		// Очистка поля ввода файлов после завершения загрузки
		fileInput.value = "";
	}
});
*/


document.querySelector("#schemForm").addEventListener("change", function(e) {
    if (e.target && e.target.classList.contains("fileInput")) {
        var fileInput = e.target;		
        var fileInputGroup = fileInput.closest('.fields');
        var fileList = fileInputGroup.parentNode.querySelector(".fileList");

        var files = fileInput.files;

        var fieldId = fileInput.getAttribute("data-fieldid");
        var baseName = fileInput.getAttribute("data-basename");
        var itemId = fileInput.getAttribute("data-itemid");

        for (var i = 0; i < files.length; i++) {
			const file = files[i];
            const fileName = file.name.toLowerCase();

			// Файл с расширением HEIC/HEIF, вероятно, требует конвертации
			if (fileName.endsWith(".heic") || fileName.endsWith(".heif")) {
                
				// Создаем сообщение о конвертации
                const conversionMessage = document.createElement("div");
				conversionMessage.className = ("ui segment");
                conversionMessage.textContent = `Подготовка файла ${file.name}...`;
                fileList.insertBefore(conversionMessage, fileList.firstChild);

                heic2any({
                    blob: file,
                    toType: "image/jpeg",
                }).then((convertedBlob) => {
                    const convertedFile = new File([convertedBlob], file.name.replace(/\.[^/.]+$/, ".jpg"), { type: "image/jpeg" });

					// Убираем сообщение о конвертации
                    conversionMessage.remove();

                    uploadFile(convertedFile, fileList, fieldId, baseName, itemId);
                }).catch((error) => {
                    console.error("Ошибка конвертации HEIC в JPG:", error);
                });
            } else {
                // Если файл не в формате HEIC, загружаем его напрямую
                uploadFile(file, fileList, fieldId, baseName, itemId);
            }

            //uploadFile(file, fileList, fieldId, baseName, itemId);
        }

        // Очистка поля ввода файлов после завершения загрузки
        fileInput.value = "";
    }
});

function uploadFile(file, fileList, fieldId, baseName, itemId) {
	var formData = new FormData();
	formData.append("file", file);
	formData.append("fieldId", fieldId);
	formData.append("baseName", baseName);
	formData.append("itemId", itemId);

	var listItem = document.createElement("div");
	listItem.className = ("ui clearing segment");
	listItem.textContent = file.name + " ";

	var progressSpan = document.createElement("span");
	progressSpan.textContent = "0%";
	listItem.appendChild(progressSpan);
	fileList.insertBefore(listItem, fileList.firstChild);

	// Установка начального фона
	listItem.style.background = `linear-gradient(to right, lightgreen 0%, white 0%)`;

	var xhr = new XMLHttpRequest();
	xhr.open("POST", "/upload.php", true);

	xhr.upload.addEventListener("progress", function(e) {
		if (e.lengthComputable) {
			var percentComplete = Math.round((e.loaded / e.total) * 100);
			progressSpan.textContent = percentComplete + "%";
			listItem.style.background = `linear-gradient(to right, lightgreen ${percentComplete}%, white ${percentComplete}%)`;

			if (percentComplete === 100) {
				progressSpan.remove();
			}
		}
	});

	xhr.addEventListener("load", function() {
		handleXhrCompletion(xhr, fileList, listItem, file, progressSpan);
	});

	xhr.addEventListener("error", function() {
		handleXhrCompletion(xhr, fileList, listItem, file, progressSpan);        
	});

	xhr.send(formData);
}

function handleXhrCompletion(xhr, fileList, listItem, file, progressSpan) {
	if (progressSpan) {
		progressSpan.remove();
	}

	try {
        var result = JSON.parse(xhr.responseText);

        if (xhr.status == 200) {
            if (result.success) {
                listItem.style.background = "none";
                //listItem.textContent = result.originalFileName + " " + JSON.stringify(result);
                listItem.textContent = result.originalFileName;
                addHiddenInput(listItem, result.id, result.fieldId);
            } else {
                listItem.style.background = "#FCEBEB";
                listItem.textContent = file.name + " Ошибка загрузки: " + result.error;
            }
        } else {
            listItem.style.background = "#FCEBEB";
            if (xhr.status == 413) {
                listItem.textContent = file.name + " Ошибка загрузки: Размер файла слишком большой";
            } else {
                listItem.textContent = file.name + " Ошибка загрузки";
            }
        }
    } catch (e) {
        listItem.style.background = "#FCEBEB";
        listItem.textContent = file.name + " Ошибка загрузки: Некорректный ответ сервера:" + xhr.responseText;
		//listItem.textContent = file.name + " Ошибка загрузки: Некорректный ответ сервера";
    }

	addDeleteFileButton(fileList, listItem);
}

function addDeleteFileButton(fileList, listItem) {
	var deleteButton = document.createElement("a");
	deleteButton.className = "ui right floated tiny blue icon button";
	deleteButton.title = "Удалить файл";

	var trashIcon = document.createElement("i");
	trashIcon.className = "trash icon";
	deleteButton.appendChild(trashIcon);

	deleteButton.addEventListener("click", function() {
		fileList.removeChild(listItem);
	});

	//listItem.appendChild(deleteButton);
	listItem.insertBefore(deleteButton, listItem.firstChild);
}

function addHiddenInput(listItem, id, fieldId) {
	//<input type="hidden" name="form_info[2]" value="">
    var hiddenInput = document.createElement("input");
    hiddenInput.type = "hidden";
    hiddenInput.name = "form_info[" + fieldId + "][]";
    hiddenInput.value = id;
    
    listItem.appendChild(hiddenInput);
}

// Добавляем события для удаления существующих полей
document.querySelectorAll('.removeFiles').forEach(button => {
    button.addEventListener('click', function() {
        button.closest('.file-item').remove();
    });
});