// var scheduleForm = document.getElementById('schedule-form');
var inputStartTime = document.querySelector('.input-starttime');
var inputEndTime = document.querySelector('.input-endtime');


var scheduleList = document.querySelectorAll('.schedule-li');
var scheduleInfoList = document.querySelectorAll('.schedule-info');

var addBtn = document.getElementById('add-btn');
var editBtn = document.getElementById('edit-btn');
var addAttendeesBtn = document.getElementById('add-attendees-btn');
var checkEmpList = document.querySelectorAll('.checkbox-emp');
var attendeesArea = document.getElementById('attendees-area');

console.log(attendeesArea);


var scheduleForm = document.getElementById('schedule-form');
var editForm = document.getElementById('edit-form');

addBtn.addEventListener('click', function (event) {
    if (scheduleForm.style.display == 'none') {
        var x = event.pageX;
        var y = event.pageY;
        scheduleForm.style.position = 'absolute';
        scheduleForm.style.top = (y - 100) + 'px';
        scheduleForm.style.left = (x + 100) + 'px';
        console.log(scheduleForm);
        scheduleForm.style.display = 'block';
    }
});

var attendeesList = ['自分'];
for(var checkEmp of checkEmpList){
    checkEmp.addEventListener('change', function(){
        if(this.checked){
            console.log('y');
            attendeesList.push(this.dataset.name);
            console.log(attendeesList);
        }else{
            var delIndex = attendeesList.indexOf(this.dataset.name);
            attendeesList.splice(delIndex, 1);
            console.log('n');
            console.log(attendeesList);
        }
        attendeesArea.textContent = '';
        attendeesArea.textContent = (attendeesList.join(', '));        
     })
}



for (var schedule of scheduleList) {
    console.log(schedule);
    schedule.addEventListener('click', function (event) {
        console.log(this);
        var x = event.pageX;
        var y = event.pageY;
        console.log(x);
        console.log(y);

        for (var scheduleInfo of scheduleInfoList) {
            scheduleInfo.style.display = 'none';
        }
        console.log(this.nextElementSibling);
        var targetInfo = this.nextElementSibling;

        targetInfo.style.position = 'absolute';
        targetInfo.style.left = x + 'px';
        targetInfo.style.top = y + 'px';
        targetInfo.style.display = 'block';

        editBtn.addEventListener('click', function () {

            targetInfo.style.display = 'none';
            editForm.style.position = 'absolute';
            editForm.style.left = x + 'px';
            editForm.style.top = y + 'px';
            editForm.style.display = 'block';
            console.log(editForm);
            //        this.nextElementSibling
        });
    });
}




scheduleForm.addEventListener('submit', function (e) {
    var inputDate = this.querySelector('.input-date');
    var inputTitle = this.querySelector('.input-title');
    var date = new Date(inputDate.value);
    var today = new Date(getToday());

    // スケジュール登録エラーチェック
    if (date.getTime() < today.getTime()) {
        window.alert("本日以降の日付を選択してください。");
        e.preventDefault();
    }
    if (inputTitle.value.length === 0) {
        window.alert("タイトルを入力してください。");
        e.preventDefault();
    }
    if (inputStartTime.value >= inputEndTime.value) {
        window.alert("終了時刻を開始時刻より遅く設定してください。");
        e.preventDefault();
    }
});


inputStartTime.addEventListener('change', function () {
    inputEndTime.value = inputStartTime.value;
})


function getToday() {
    var date = new Date();
    var y = date.getFullYear();
    var m = ("00" + (date.getMonth() + 1)).slice(-2);
    var d = ("00" + date.getDate()).slice(-2);
    return (y + "-" + m + "-" + d);
}