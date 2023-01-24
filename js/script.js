"use strict";

$(function () {
  // 検索結果の表示
  var $searchBtn = $(".search-btn");
  $searchBtn.on("click", function (e) {
    e.preventDefault();
    var keyword = $(this).parent().find(".input-keyword").val();
    var deptId = $(this).parent().find(".select-dept").val();
    var $empTable = $(this).parent().find(".emp-table");
    var checkedId = $(this).data("checked");

    $.ajax({
      type: "POST",
      url: "ajax/search_ajax.php",
      data: {
        "dept_id": deptId,
        "keyword": keyword,
        "checked_list": checkedId
      }
    }).done(function (data) {
      $empTable.html(data);
      $empTable.css("display", "block");
    }).fail(function () {
      alert("ajax failed");
    });

  });

  // スケジュール詳細画面の表示
  var $scheduleItem = $(".schedule-item");
  var $scheduleDetail = $("#schedule-detail");
  var $detailTable = $(".detail-table");
  var $form = $("#schedule-form");
  var $editBtn = $(".edit-btn");
  var $searchAttendees = $(".search-attendees");

  $scheduleItem.on("click", function (e) {
    var scheduleId = $(this).data("id");
    $.ajax({
      type: "POST",
      url: "ajax/form_ajax.php",
      data: {
        "schedule_id": scheduleId,
        "form_flag": "0"
      }
    }).done(function (data) {
      $editBtn.data("id", scheduleId);
      $detailTable.html(data);
      var isAttendee = $detailTable.find(".is-attendee").val();
      if (isAttendee == 1) $editBtn.css("display", "block");
      else $editBtn.css("display", "none");
      $form.css("display", "none");
      $scheduleDetail.css("display", "block");

    }).fail(function () {
      alert("ajax failed");
    });
  });

  // スケジュール編集フォームの表示
  var $formTable = $(".form-table");
  var $delBtn = $(".delete-btn");
  var $editAttendeeBtn = $(".edit-attendee-btn");
  var $formInputId = $form.find(".input-schedule-id");

  $editBtn.on("click", function (e) {
    formReset(); // フォーム初期化
    var scheduleId = $(this).data("id"); // クリックしたスケジュールのidを取得

    $.ajax({
      type: "POST",
      url: "ajax/form_ajax.php",
      data: {
        "schedule_id": scheduleId,
        "form_flag": "1"
      }
    }).done(function (data) {
      $formTable.html(data);
      $scheduleDetail.css("display", "none");
      $delBtn.css("display", "inline-block");
      $form.css("display", "block");
      $formTitle.text("スケジュール編集");
      $formSubmitBtn.text("更新");
      $formSubmitBtn.attr("name", "submit_edit");
      $formInputId.val(scheduleId);

      // チェック済みの社員のデータを取得しておく
      var $checkedList = $form.find(".checked-list").data("checked");
      $editAttendeeBtn.data("checked", $checkedList);
    }).fail(function () {
      alert("ajax failed");
    });
  });

  // 新規スケジュール登録フォーム表示
  var $addBtn = $("#add-btn");
  var $formTitle = $form.find(".form-title");
  var $formSubmitBtn = $form.find(".submit-btn");
  var $formSearchBtn = $form.find(".search-btn");
  $addBtn.on("click", function () {
    formReset(); // フォーム初期化

    $.ajax({
      type: "POST",
      url: "ajax/form_ajax.php",
      data: {
        "schedule_id": "0",
        "form_flag": "1"
      }
    }).done(function (data) {
      $formTable.html(data);
      $scheduleDetail.css("display", "none");
      $delBtn.css("display", "none");
      $formTitle.text("新規スケジュール登録");
      $formSubmitBtn.text("登録");
      $formSubmitBtn.attr("name", "submit_add");
      $form.css("display", "block");
    }).fail(function () {
      alert("ajax failed");
    });
  });

  // 新規登録・編集フォーム用の検索画面の表示
  $editAttendeeBtn.on("click", function (e) {
    e.preventDefault();
    var checkedList = $editAttendeeBtn.data("checked");
    $formSearchBtn.data("checked", checkedList);
    $(this).parent().parent().append($searchAttendees);
    $searchAttendees.css("display", "block");
  });

  // 表示された社員の空き時間を表示
  var freeTimeBtn = $("#free-time-btn");
  var displayFreeTime = $('#display-free-time');
  var freeTimeTable = $('.free-time-table');
  freeTimeBtn.on("click", function () {
    var displayEmpList = $(this).data("id");
    var week = $(this).data("week");

    $.ajax({
      type: "POST",
      url: "ajax/freetime_ajax.php",
      data: {
        displayEmpList: displayEmpList,
        week: week
      }
    }).done(function (data) {
      freeTimeTable.html(data);
      displayFreeTime.css("display", "block");

    }).fail(function () {
      alert("ajax failed");
    });

  });

  // フォーム初期化処理
  function formReset() {
    $searchAttendees.css("display", "none");
    $searchAttendees.find(".emp-table").empty();
    $form.find(".input-keyword").val("");
    $editAttendeeBtn.data("checked", "");
    $formInputId.val("");
  }
});

// フォーム内検索時、エンターキーでのフォーム送信を無効化
var form = document.getElementById("schedule-form");
form.addEventListener("keydown", function (e) {
  if (e.code === "Enter") e.preventDefault();
});


// スケジュール登録・更新フォームの入力チェック
form.addEventListener('submit', function (e) {
  var inputTitle = this.querySelector('input[name="title"]');
  var inputDate = this.querySelector('input[name="date"]');
  var inputStartTime = this.querySelector('input[name="start"]');
  var inputEndTime = this.querySelector('input[name="end"]');

  var error = [];
  if (inputTitle.value === "") {
    error.push("入力エラー：タイトルを入力してください。");
  }
  if (inputDate.value === "" || inputStartTime === "" || inputEndTime === "") {
    error.push("入力エラー：日時を入力してください。");
  } else {
    var now = new Date();
    var startTime = new Date(inputDate.value + " " + inputStartTime.value);
    var endTime = new Date(inputDate.value + " " + inputEndTime.value);
    if (startTime.getTime() < now.getTime()) {
      error.push("入力エラー：日時を現在よりも遅く設定してください。");
    }
    if (startTime.getTime() >= endTime.getTime()) {
      error.push("入力エラー：終了時刻を開始時刻より遅く設定してください。");
    }
  }
  if (error.length > 0) {
    window.alert(error.join("\n"));
    e.preventDefault();
  }
});

// スケジュール削除確認
var delBtn = document.querySelector(".delete-btn");
delBtn.addEventListener("click", function (e) {
  var res = window.confirm("スケジュールを削除しますか？");
  if (res === false) e.preventDefault();
})

// 「できること」を表示
var displayGuide = document.getElementById("display-guide");
var guideBtn = document.getElementById("guide-btn");
guideBtn.addEventListener("click", function (e) {
  displayGuide.style.display = "block";
});

// ×ボタンを押すと閉じる
var closeBtnList = document.querySelectorAll(".close-btn");
for (var closeBtn of closeBtnList) {
  closeBtn.addEventListener("click", function () {
    var parent = this.closest(".display-elem");
    parent.style.display = "none";
  });
}