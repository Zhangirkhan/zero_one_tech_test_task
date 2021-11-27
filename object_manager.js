$(function() {
    ymaps.ready(init);

    function init() {
        var myPlacemark,
            myMap = new ymaps.Map('map', {
                center: [43.242673, 76.956335],
                zoom: 11,
                controls: ['zoomControl', 'typeSelector', 'fullscreenControl', ]
            }, {
                searchControlProvider: 'yandex#search'
            });

        // Слушаем клик на карте.
        myMap.events.add('click', function(e) {
            var coords = e.get('coords');

            // Если метка уже создана – просто передвигаем ее.
            if (myPlacemark) {
                myPlacemark.geometry.setCoordinates(coords);
            }
            // Если нет – создаем.
            else {
                myPlacemark = createPlacemark(coords);
                myMap.geoObjects.add(myPlacemark);
                // Слушаем событие окончания перетаскивания на метке.
                myPlacemark.events.add('dragend', function() {
                    getAddress(myPlacemark.geometry.getCoordinates());
                });
            }
            getAddress(coords);
        });

        // Создание метки.
        function createPlacemark(coords) {
            return new ymaps.Placemark(coords, {
                iconCaption: 'поиск...'
            }, {
                preset: 'islands#violetDotIconWithCaption',
                draggable: true
            });
        }

        // Определяем адрес по координатам (обратное геокодирование).
        function getAddress(coords) {
            myPlacemark.properties.set('iconCaption', 'поиск...');
            ymaps.geocode(coords).then(function(res) {
                var firstGeoObject = res.geoObjects.get(0);

                myPlacemark.properties
                    .set({
                        // Формируем строку с данными об объекте.
                        iconCaption: [
                            // Название населенного пункта или вышестоящее административно-территориальное образование.
                            firstGeoObject.getLocalities().length ? firstGeoObject.getLocalities() : firstGeoObject.getAdministrativeAreas(),
                            // Получаем путь до топонима, если метод вернул null, запрашиваем наименование здания.
                            firstGeoObject.getThoroughfare() || firstGeoObject.getPremise()
                        ].filter(Boolean).join(', '),
                        // В качестве контента балуна задаем строку с адресом объекта.
                        balloonContent: firstGeoObject.getAddressLine()
                    });
            });
        }


        // Создаем коллекцию, и массив для хранения наших точек.
        myCollection = new ymaps.GeoObjectCollection(), myPoints = [];

        //Получаем json точик
        $.getJSON("/api/read.php", function(data) {
            myPoints = data;
            // Заполняем коллекцию данными.
            for (var i = 0, l = myPoints.length; i < l; i++) {
                var point = myPoints[i];
                myCollection.add(new ymaps.Placemark(
                    point.coords, {
                        balloonContentBody: point.text
                    }
                ));
            }
            // Добавляем коллекцию меток на карту.
            myMap.geoObjects.add(myCollection);
        });
    }
});

//Модуль поиска по адресу и получение координат

$(function() {
    ymaps.ready(init2);

    function init2() {
        // Подключаем поисковые подсказки к полю ввода.
        var suggestView = new ymaps.SuggestView('suggest'),
            map,
            placemark;

        // При клике по кнопке запускаем верификацию введёных данных.
        $('#button').bind('click', function(e) {
            geocode();
        });

        function geocode() {
            // Забираем запрос из поля ввода.
            var request = $('#suggest').val();
            // Геокодируем введённые данные.
            ymaps.geocode(request).then(function(res) {
                var obj = res.geoObjects.get(0),
                    error, hint;

                if (obj) {
                    // Об оценке точности ответа геокодера можно прочитать тут: https://tech.yandex.ru/maps/doc/geocoder/desc/reference/precision-docpage/
                    switch (obj.properties.get('metaDataProperty.GeocoderMetaData.precision')) {
                        case 'exact':
                            break;
                        case 'number':
                        case 'near':
                        case 'range':
                            error = 'Неточный адрес, требуется уточнение';
                            hint = 'Уточните номер дома';
                            break;
                        case 'street':
                            error = 'Неполный адрес, требуется уточнение';
                            hint = 'Уточните номер дома';
                            break;
                        case 'other':
                        default:
                            error = 'Неточный адрес, требуется уточнение';
                            hint = 'Уточните адрес';
                    }
                } else {
                    error = 'Адрес не найден';
                    hint = 'Уточните адрес';
                }

                // Если геокодер возвращает пустой массив или неточный результат, то показываем ошибку.
                if (error) {
                    showError(error);
                    showMessage(hint);
                } else {
                    showResult(obj);
                }
            }, function(e) {
                console.log(e)
            })
        }

        function showResult(obj) {
            // Удаляем сообщение об ошибке, если найденный адрес совпадает с поисковым запросом.
            $('#suggest').removeClass('input_error');
            $('#notice').css('display', 'none');

            // Сохраняем полный адрес для сообщения под картой.
            address = [obj.getCountry(), obj.getAddressLine()].join(', ');

            //Из объекта берем координаты
            сoord = obj.geometry.getCoordinates();

            //Показываем форму
            $('#footer').css('display', 'block');
            //Вставляем в инпуты значения
            document.getElementById("address").setAttribute('value', address);
            document.getElementById("latitude").setAttribute('value', сoord[0]);
            document.getElementById("longitude").setAttribute('value', сoord[1]);

            //Есть проблема jquery val не вставляет в html значенние
            // $('#address').val(address);
        }

        function showError(message) {
            $('#notice').text(message);
            $('#suggest').addClass('input_error');
            $('#notice_footer').css('display', 'block');
            $('#notice').css('display', 'block');
        }

        function showMessage(message) {
            $('#notice').css('display', 'block');
            $('#message').text(message);
        }
    }

});

// Отдельная функция для сериализации объекта
$.fn.serializeObject = function() {
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
        if (o[this.name]) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};


// будет работать, если создана форма адреса
$(document).on('click', '#save_address', function() {
    // получение данных формы
    var form_data = JSON.stringify($('#create-address-form').serializeObject());
    // отправка данных формы в API
    $.ajax({
        url: "/api/create.php",
        type: "POST",
        contentType: 'application/json',
        data: form_data,
        success: function(result) {
            // адрес был добавлен
            console.log(result);
            alert("Адрес добавлен");
            document.location.reload();
        },
        error: function(xhr, resp, text) {
            // вывести ошибку в консоль
            console.log(xhr, resp, text);
        }
    });

    return false;
});

function deleteAddress(id) {
    if (confirm('Удаляем адрес?')) {
        $.ajax({
            url: "/api/delete.php",
            type: "POST",
            dataType: 'json',
            data: JSON.stringify({ id: id }),
            success: function(result) {
                alert("Адрес успешно удален");
                document.location.reload();
            },
            error: function(xhr, resp, text) {
                console.log(xhr, resp, text);
            }
        });
    } else {
        alert("Адрес не удален");
    }
}