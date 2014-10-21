/*
    文件上传框
*/
function copy_upload() {
    var upload_ele,uploaddiv,upload_form;
    upload_form = document.getElementById("uploadform");
    uploaddiv = document.getElementById("uploaddiv");
    new_node = uploaddiv.cloneNode(true);
    upload_form.insertBefore(new_node,uploaddiv);
}
/*
    选择所有的复选框
*/
function select_all(name) {
    var allcheckbox = document.getElementsByName(name);
    for (var i = 0;i < allcheckbox.length;i++) {
        allcheckbox[i].checked = true;
    }
    return true;
}
/*
    将所有的选择提交到表单
*/
function post_all(formName) {
    var postform = document.getElementById(formName);
    postform.submit();
}
/*
	
*/
function de_select(name) {
	var allcheckbox = document.getElementsByName(name);
    for (var i = 0;i < allcheckbox.length;i++) {
        allcheckbox[i].checked = !allcheckbox[i].checked;
    }
    return true;	
}