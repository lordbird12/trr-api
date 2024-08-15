<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFactoryActivityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('factory_activity', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('trans_id')->unsigned()->nullable();//ไอดีเกษตรกร
            //ใช้ร่วมกันทุกตัว
            $table->integer('frammer_id')->unsigned()->nullable();//ไอดีเกษตรกร
            $table->double('areasize')->nullable();//ค่าจ้างแรงงาน
            // $table->foreign('frammer_id')->references('id')->on('frammers')->onDelete('cascade');
            $table->string('sugartype');//ประเภทน้ำตาล
            $table->integer('plotsugar_id')->unsigned()->nullable();//ไร่ที่เลือก
            $table->string('activitytype')->nullable();//ประเภทกิจกรรม
            $table->integer('NO')->unsigned()->nullable()->default(0);//ไอดีเกษตรกร
            $table->string('selectdate')->nullable();//วันที่เลือกกิจกรรม
            $table->string('image', 255)->charset('utf8')->nullable();//รูปภาพ
            
            //ใช้ร่วมกันบางตัว
            $table->double('fuelcost')->nullable();//ค่าเชื้อเพลิง
            $table->double('laborwages')->nullable();//ค่าจ้างแรงงาน
            $table->double('fertilizercost')->nullable();//ค่าปุ๋ยรองพื้น
            $table->string('fertilizerquantity')->nullable();//ปริมาณปุ๋ย

            //ไถและเตรียมดิน 0
            $table->string('soilImprovement')->nullable();//ปรับปรุงดิน
            $table->string('plowingtype')->nullable();//ประเภทการไถ
            $table->string('subtypeplowing')->nullable();//ค่าด้านในการไถ
            $table->double('insecticidecost')->nullable();//ค่ากำจัดศัตรูพืช
            $table->double('equipmentrent')->nullable();//ค่าเช่าอุปกรณ์
            //ค่าจ้างแรงงาน
            //ค่าเชื้อเพลิง

            //ปลูกอ้อย 1
            $table->string('sugarcane')->nullable();//พันธ์อ้อย
            $table->string('plantingsystem')->nullable();//ระบบปลูก
            $table->string('fertilizer')->nullable();//ปุ๋ยรองพื้น
            $table->double('expenses')->nullable();//ระยะห่างระหว่างช่อง
            $table->double('sugartypecost')->nullable();//ค่าพันธ์อ้อย
            //ค่าปุ๋ยรองพื้น
            $table->double('sugarcaneplantingcost')->nullable();//ค่าปลูกอ้อย
            //ค่าเชื้อเพลิง

            //ให้น้ำ 2
            $table->string('wateringsystem')->nullable();//ประเภทการรดน้ำ
            //ค่าจ้างแรงงาน
            //ค่าเชื้อเพลิง

            //ฉีดพ่นน้ำหมักปุ๋ยยูเรีย 3
            //ปริมาณปุ๋ย
            $table->string('otheringredients')->nullable();//ส่วนผสมอื่นๆ
            $table->double('amountureafertilizer')->nullable();//ค่าปุ๋ยยูเรีย
            $table->string('herbicide')->nullable();//สารกำจัดวัชพืช
            $table->string('othertypes')->nullable();//อื่นๆ
            //ค่าปุ๋ยรองพื้น
            $table->double('otheringredientcosts')->nullable();//ค่าส่วนผสมอื่นๆ
            $table->double('herbicidecost')->nullable();//ค่ากำจัดวัชพืช
            //ค่าจ้างแรงงาน
            //ค่าเชื้อเพลิง

            //ฉีดพ่นสารกำจัดศัตรูพืช 4
            $table->string('weed')->nullable();//วัชพืช
            $table->string('plantdiseases')->nullable();//โรคพืช
            $table->string('pests')->nullable();//แมลงศัตรูพืช
            $table->double('pesticidecost')->nullable();//ค่ากำจัดศัตรูพืช
            //ค่าจ้างแรงงาน
            //ค่าเชื้อเพลิง

            //ใส่ปุ๋ย 5
            $table->string('fertilizertype')->nullable();//ประเภทปุ๋ย
            //ปริมาณปุ๋ย
            //ค่าปุ๋ยรองพื้น
            //ค่าจ้างแรงงาน
            //ค่าเชื้อเพลิง

            //ตัดอ้อย 6
            $table->string('cuttingtype')->nullable();//ประเภทการตัดอ้อย
            $table->string('sugarcanetype')->nullable();//ประเภทอ้อย
            $table->double('sugarcanecuttinglabor')->nullable();//ค่าตัดอ้อย
            //ค่าเชื้อเพลิง

            //ขนส่งอ้อย 7
            //ค่าจ้างแรงงาน
            //ค่าเชื้อเพลิง


            $table->string('create_by', 100)->nullable();
            $table->string('update_by', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('factory_activity');
    }
}
