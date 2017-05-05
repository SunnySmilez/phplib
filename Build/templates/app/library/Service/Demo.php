<?php
namespace Service;

use Base\Exception\Service as Exception;

/**
 * Class Demo
 *
 * @package Service
 * @description 业务层示范
 *
 *              备注:
 *              1. Service层封装复杂、可重用的业务逻辑, 为Controller层提供整齐、统一的业务服务入口;
 *              2. Service层能够调用Data层以及其他Service层的服务, 禁止直接调用Dao层;
 *              3. 注意服务的拆分和设计, 以及服务间的协调和交互, 保持良好设计, 便于未来扩展和持续优化;
 *              4. 属性定义优先级: 常量>静态属性>对象属性 public>protected>private
 *              5. 方法定义优先级: 静态方法>对象方法 public>protected>private
 */
class Demo {

    /**
     * 业务层示范
     *
     * 此处仅作示范, 与实际业务场景不相关, 实际编写时应注意业务流程的设计
     *
     * @param int $id
     * @param string $name
     *
     * @return string
     * @throws Exception
     */
    public function demoServiceLogic($id, $name){
        $data_demo = new \Data\Demo();
        if ($data_demo->get($id)) {
            throw new Exception("error.demo.demo_fail_check_error");
        }

        $this->someOtherLogic();

        $data = array(
            'id' => $id,
            'name' => $name,
        );
        $data_demo->add($data);

        return $name;
    }

    /**
     * 业务层其他逻辑
     *
     * @return bool
     */
    private function someOtherLogic() {
        //业务逻辑

        return true;
    }

}