/*
    �ļ��ϴ���
*/
function copy_upload() {
    var upload_ele,uploaddiv,upload_form;
    upload_form = document.getElementById("uploadform");
    uploaddiv = document.getElementById("uploaddiv");
    new_node = uploaddiv.cloneNode(true);
    upload_form.insertBefore(new_node,uploaddiv);
}
/*
    ѡ�����еĸ�ѡ��
*/
function select_all(name) {
    var allcheckbox = document.getElementsByName(name);
    for (var i = 0;i < allcheckbox.length;i++) {
        allcheckbox[i].checked = true;
    }
    return true;
}
/*
    �����е�ѡ���ύ����
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