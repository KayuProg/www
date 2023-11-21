/*===============================================================
        画像の数変更時には総画像数に変更
===============================================================*/

/*画像の数*/
let to_left_num = 7
let to_right_num = 7

/*===============================================================
===============================================================*/


/*left right終わった時に処理を飛ばす*/
let left_cnt = [];
let right_cnt = [];


/*関数を終了するためのチェック*/
let left_check = 0;
let right_check = 0;
let set_cont = null;

/*ランダム数生成*/
function random(min, max) {
    let cnt = Math.floor(Math.random() * (max + 1 - min) + min);
    return cnt;
};
;

function to_left_fun(num) {
    let to_left = document.getElementById(`home_left_img${num}`);
    // if (to_left.classList.contains("to_left")){
    //     to_left.classList.remove('to_left');
    // };
    to_left.style.display = 'block';
    to_left.classList.add('to_left');
};

function to_right_fun(num) {
    let to_right = document.getElementById(`home_right_img${num}`);
    // if (to_right.classList.contains("to_right")){
    //     to_right.classList.remove('to_right');
    // };
    to_right.style.display = 'block';
    to_right.classList.add('to_right');
};


function home_slide() {
    /*右か左を決める*/
    let either = random(0, 1);
    /*実行*/
    if (either == 0) {
        let num = random(1, to_left_num);
        let len = left_cnt.length;
        while (1) {
            if (len >= to_left_num) {
                // console.log('left become full');
                left_check = 1;
                // console.log('left_check is 1');
                set_inter_finish();
                break
            }
            ;
            if (left_cnt.includes(num)) {
                num = random(1, to_left_num);
            } else {
                break
            }
        }
        // console.log(`TO LEFT ${num}.png pushed`);
        left_cnt.push(num);
        to_left_fun(num);
    }
    ;
    if (either == 1) {
        let num = random(1, 7);
        let len = right_cnt.length;
        while (1) {
            if (len >= to_right_num) {
                // console.log('right become full');
                right_check = 1;
                // console.log('right_check is 1');
                set_inter_finish();
                break
            }
            ;
            if (right_cnt.includes(num)) {
                num = random(1, to_right_num);
            } else {
                break
            }
        }
        // console.log(`TO RIGHT ${num}.png pushed`);
        right_cnt.push(num);
        to_right_fun(num);
    }
    ;

};

function set_inter_finish() {
    if (left_check == 1 && right_check == 1) {
        clearInterval(set_cont);
        // console.log('airplane slide finished')
    }
    ;
}

function set_inter_controll() {
    // console.log('airplane slide started')
    set_cont = setInterval(home_slide, 5000);
    // console.log(set_cont);
};

set_inter_controll();
