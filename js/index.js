// var scheduleForm = document.getElementById('schedule-form');
var inputStartTime = document.querySelector('.input-starttime');
var inputEndTime = document.querySelector('.input-endtime');


var scheduleItemList = document.querySelectorAll('.schedule-item');
var scheduleDescList = document.querySelectorAll('.schedule-desc');

var addBtn = document.getElementById('add-btn');

var editBtnList = document.querySelectorAll('.edit-btn');

var addAttendeesBtn = document.getElementById('add-attendees-btn');
var checkEmpList = document.querySelectorAll('.checkbox-emp');
var attendeesArea = document.getElementById('attendees-area');

console.log(attendeesArea);


var addForm = document.getElementById('add-form');

//var editForm = document.getElementById('edit-form');


// 新規追加ボタンを押すとフォームを表示
addBtn.addEventListener('click', function (event) {
    if (addForm.style.display == 'none') {
        var x = event.pageX;
        var y = event.pageY;
        addForm.style.position = 'absolute';
        addForm.style.top = (y - 100) + 'px';
        addForm.style.left = (x + 100) + 'px';
        console.log(addForm);
        addForm.style.display = 'block';
    }
});

var closeCheck = document.getElementById('close-check');
/*
var closeBtnList = document.querySelectorAll('.close-btn');
for(var closeBtn of closeBtnList){
    closeBtn.addEventListener('click', function(){
        closeCheck.value = "valid";

    })
}
*/


// 参加者のチェックに連動して表示も変動
var attendeesList = ['自分'];
for (var checkEmp of checkEmpList) {
    checkEmp.addEventListener('change', function () {
        if (this.checked) {
            console.log('y');
            attendeesList.push(this.dataset.name);
            console.log(attendeesList);
        } else {
            var delIndex = attendeesList.indexOf(this.dataset.name);
            attendeesList.splice(delIndex, 1);
            console.log('n');
            console.log(attendeesList);
        }
        attendeesArea.textContent = '';
        attendeesArea.textContent = (attendeesList.join(', '));
    })
}


// スケジュールを押すと詳細を表示
for (var scheduleItem of scheduleItemList) {
    console.log(scheduleItem);
    scheduleItem.addEventListener('click', function (event) {

        console.log('y');
        /*
        for (var scheduleDesc of scheduleDescList) {
            scheduleDesc.style.display = 'none';
        }
        */
        //  console.log(this.nextElementSibling);
        var scheduleDesc = this.nextElementSibling;
        var editBtn = scheduleDesc.querySelector('.edit-btn');
        var scheduleCloseBtn = scheduleDesc.querySelector('.close-btn');
        var editForm = scheduleDesc.nextElementSibling;
        var editCloseBtn = editForm.querySelector('.close-btn');

        console.log(editCloseBtn);

        var x = event.pageX;
        var y = event.pageY;

        console.log(editForm);

        if (closeCheck.value === 'valid') {
            closeCheck.value = 'invalid';
            scheduleDesc.style.position = 'absolute';
            scheduleDesc.style.left = x + 'px';
            scheduleDesc.style.top = y + 'px';
            scheduleDesc.style.display = 'block';
        }
        editBtn.addEventListener('click', function () {
            console.log(editForm);
            scheduleDesc.style.display = 'none';
            editForm.style.position = 'absolute';
            editForm.style.left = x + 'px';
            editForm.style.top = y + 'px';
            editForm.style.display = 'block';
        });
 
        scheduleCloseBtn.addEventListener('click', function(){
            closeCheck.value = 'valid';
            scheduleDesc.style.display = 'none';
        })
        
        editCloseBtn.addEventListener('click', function(){
            closeCheck.value = 'valid';
            editForm.style.display = 'none';
        })
        



        /*
        editBtn.addEventListener('click', function () {

            targetInfo.style.display = 'none';
            editForm.style.position = 'absolute';
            editForm.style.left = x + 'px';
            editForm.style.top = y + 'px';
            editForm.style.display = 'block';
            console.log(editForm);
            //        this.nextElementSibling
        });
        */
    });
}




// スケジュール追加前の入力エラーチェック
addForm.addEventListener('submit', function (e) {
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