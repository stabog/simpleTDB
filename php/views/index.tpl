<!DOCTYPE HTML>
<html>
<head>
    <title><?= $content["title"] ?></title>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"> 
    <meta name="description" content="<?= $content["description"] ?>"> 
    <meta name="viewport" content="width=device-width, initial-scale=1"> 

    <link rel="icon" href="/img/favicon.png">

    <!-- Required -->
    <script src="/js/jquery.min.js"></script>

    <!-- Semantic UI -->
    <link rel="stylesheet" type="text/css" href="/vendor/fomantic/ui/dist/semantic.min.css">
    <script src="/vendor/fomantic/ui/dist/semantic.min.js"></script>

    <link rel="stylesheet" type="text/css" href="/css/style.css?v=24-06-22">
    
</head>
<body>



<?

echo $html;

    
?>

                
<script src="/js/lightbox2/js/lightbox.js"></script>
<link href="/js/lightbox2/css/lightbox.min.css" rel="stylesheet" />




<script type="text/javascript" charset="utf-8">



$('.ui.checkbox').checkbox();
$('#progress-bar').progress();

$('.message .close')
  .on('click', function() {
    $(this)
      .closest('.message')
      .transition('fade')
    ;
  })
;

$('.menu .item')
  .tab()
;

$('.ui.dropdown')
  .dropdown()
;


var today = new Date();

$('.ui.calendar.datetime').calendar({
  today: true,
  ampm : false,
  firstDayOfWeek: 1,
  text: {
    days: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
    months: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
    monthsShort: ['Янв', 'Фев', 'Март', 'Апр', 'Май', 'Июнь', 'Июль', 'Авг', 'Сен', 'Окт', 'Нояб', 'Дек'],
    today: 'Сегодня',
    now: 'Сейчас',
    am: 'AM',
    pm: 'PM'
  },
       
    //minDate: new Date(today.getFullYear(), today.getMonth(), today.getDate()),
    //maxDate: new Date(today.getFullYear(), today.getMonth(), today.getDate() + 120),
		
    formatter: {
      datetime: 'DD.MM.YYYY HH:mm',
      cellTime: 'HH:mm',

      /*
      cell: function (cell, date, cellOptions) {}
      // Every other method can either be a token string
      // or a function(date,settings) which has to return a string
      cellTime: 'h:mm A',
      date: 'DD.MM.YYYY',
      datetime: 'MMMM D, YYYY h:mm A',
      dayColumnHeader: function (day, settings) {},
      dayHeader: 'MMMM YYYY',
      hourHeader: 'MMMM D, YYYY',
      minuteHeader: 'MMMM D, YYYY',
      month: 'MMMM YYYY',
      monthHeader: 'YYYY',
      time: 'h:mm A',
      today: function (settings) {},
      year: 'YYYY',
      yearHeader: function (date, settings) {},
      */        
    },

});

$('.ui.calendar.date').calendar({
        type: 'date',
        firstDayOfWeek: 1,
        text: {
        days: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
        months: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
        monthsShort: ['Янв', 'Фев', 'Март', 'Апр', 'Май', 'Июнь', 'Июль', 'Авг', 'Сен', 'Окт', 'Нояб', 'Дек'],
        today: 'Сегодня',
        now: 'Сейчас',
        am: 'AM',
        pm: 'PM'
    },
       
    //minDate: new Date(today.getFullYear(), today.getMonth(), today.getDate()),
    //maxDate: new Date(today.getFullYear(), today.getMonth(), today.getDate() + 120),
		
    formatter: {
        date: 'DD.MM.YYYY',
    },

});
	


[].forEach.call(document.querySelectorAll('.showModal'), function(item) {  
    item.onclick = function(){
        var id = $(this).attr('href');
        console.log(id);
        $(id).modal('show');
        return false; 
  };
});

[].forEach.call(document.querySelectorAll('.showHide'), function(item) {  
    item.onclick = function(){
        //console.log($(this));
        var id = $(this).attr('href').substr(1);
        document.getElementById(id).classList.toggle('hidden');
        console.log(id);        
        return false; 
  };
});



/*
Добавление полей в форму
Код поля должен формироваться функцией createNewFieldHTML(index)

document.querySelectorAll('.dynamic-form').forEach(form => {
    form.querySelector('.addFieldButton').addEventListener('click', function() {
        let container = form.querySelector('.fields-container');
        let lastField = container.querySelector('.form-item:last-of-type');
        let index = lastField ? parseInt(lastField.getAttribute('data-index')) + 1 : 1; // Обновляем индекс
        
        // Добавляем новый HTML в контейнер
        container.insertAdjacentHTML('beforeend', createNewFieldHTML(index));    

        let newField = container.lastElementChild;
        
        // Добавляем событие для удаления
        newField.querySelector('.removeField').addEventListener('click', function() {
            newField.remove();
        });

        // Добавляем событие для отображения скрытых полей
        newField.querySelector('.showHide').addEventListener('click', function() {
            toggleHiddenFields(newField);
        });

        // Добавляем событие для изменения типа фильтра
        newField.querySelector('.type-select').addEventListener('change', function() {
            toggleAdditionalFields(newField);
        });
    });

    // Добавляем события для удаления существующих полей
    form.querySelectorAll('.removeField').forEach(button => {
        button.addEventListener('click', function() {
            button.closest('.form-item').remove();
        });
    });

    form.querySelectorAll('.type-select').forEach(select => {
        select.addEventListener('change', function() {
            toggleAdditionalFields(this.closest('.inline.fields'));
        });
    });
});
*/


const addButton = document.getElementById('addFieldButton');
if (addButton) {
    addButton.addEventListener('click', function() {
        let container = document.getElementById('fieldsContainer');
        let lastField = container.querySelector('.form-item:last-of-type');
        let index = lastField ? parseInt(lastField.getAttribute('data-index')) + 1 : 1; // Обновляем индекс

        // Добавляем новый HTML в контейнер
        container.insertAdjacentHTML('beforeend', createNewFieldHTML(index));    

        let newField = container.lastElementChild;

        // Добавляем событие для удаления
        newField.querySelector('.removeField').addEventListener('click', function() {
            newField.remove();
        });

        // Добавляем событие для отображения скрытых полей
        newField.querySelector('.showHide').addEventListener('click', function() {
            toggleHiddenFields(newField);
        });

        // Добавляем событие для изменения типа фильтра
        newField.querySelector('.type-select').addEventListener('change', function() {
            toggleAdditionalFields(newField);
        });
    });
}


// Добавляем события для удаления существующих полей
document.querySelectorAll('.removeField').forEach(button => {
    button.addEventListener('click', function() {
        button.closest('.form-item').remove();
    });
});

// Function to toggle additional fields
function toggleHiddenFields(field) {
    let index = field.getAttribute('data-index');
    let hiddenFields = document.getElementById('schem_block_' + index);
    hiddenFields.classList.toggle('hidden');
}


document.querySelectorAll('.type-select').forEach(select => {
    select.addEventListener('change', function() {
        toggleAdditionalFields(this.closest('.inline.fields'));
    });
});

function toggleAdditionalFields(fieldContainer) {
    let typeSelect = fieldContainer.querySelector('.type-select');
    let additionalFields = fieldContainer.querySelector('.additional-fields');    
    
    if (typeSelect.value == "1" || typeSelect.value == "2" || typeSelect.value == "7" || typeSelect.value == "8") {
        additionalFields.style.display = 'flex';
    } else {
        additionalFields.style.display = 'none';
    }
}

</script>
    
    

</body>
</html>