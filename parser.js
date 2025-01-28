function clickSortButton() {
    // Получаем таблицу
    const gridTable = document.getElementById('b24_partner_application_table');

    // Получаем кнопку сортировки по дате
    const dateSortButton = gridTable.querySelector(
        '.main-grid-cell-head[data-name="DATE_ACTIVE"]'
    );

    //console.log(dateSortButton)
    // Кликаем на кнопку 2 раза с интервалом 1 секунду
    dateSortButton.click();
    setTimeout(() => dateSortButton.click(), 1000);
}

function readDescriptions() {
    // Получаем все элементы с описанием

    const descriptions = document.querySelectorAll(
        '.partner-application-b24-list-description-inner'
    );

    // Выводим содержимое в консоль
    descriptions.forEach((description) => {
        console.log(description.textContent.trim());
    });
}

var conn = new WebSocket('ws://localhost:8080/chat');
conn.onmessage = function(e) { console.log(e.data); };
conn.onopen = function(e) {
    console.log('Подключение по вебсокетам')
    // Запускаем цикл с интервалом 5 секунд
    setInterval(() => {
        clickSortButton()
        setTimeout(readDescriptions, 2000); // Ждем 2 секунды, чтобы данные обновились
    }, 5000);
};



