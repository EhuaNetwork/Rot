@extends("Admin.common")
@section("content")
    <style>
        .layui-table-view .layui-table {
            position: relative;
            width: 100%;
            margin: 0;
        }
    </style>
    <div class="layui-card layuimini-page-header layui-hide-xs">
        <div class="layui-breadcrumb" id="layuimini-page-header" style="visibility: visible;"><a lay-href="" href="/"
                                                                                                 one-link-mark="yes">主页</a><span
                lay-separator="">/</span>
            <a one-link-mark="yes"><cite>常规管理</cite></a><span lay-separator="">/</span>
            <a one-link-mark="yes"><cite>表格示例</cite></a>
        </div>
    </div>
    <div class="layuimini-content-page">
        <div class="layuimini-container layui-anim layui-anim-upbit">
            <div class="layuimini-main">

                <fieldset class="table-search-fieldset">
                    <legend>搜索信息</legend>
                    <div style="margin: 10px 10px 10px 10px">
                        <form class="layui-form layui-form-pane" action="">
                            <div class="layui-form-item">
                                <div class="layui-inline">
                                    <label class="layui-form-label">关键词</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="username" autocomplete="off" class="layui-input">
                                    </div>
                                </div>
                                {{--                                    <div class="layui-inline">--}}
                                {{--                                        <label class="layui-form-label">关键词性别</label>--}}
                                {{--                                        <div class="layui-input-inline">--}}
                                {{--                                            <input type="text" name="sex" autocomplete="off" class="layui-input">--}}
                                {{--                                        </div>--}}
                                {{--                                    </div>--}}
                                {{--                                    <div class="layui-inline">--}}
                                {{--                                        <label class="layui-form-label">关键词城市</label>--}}
                                {{--                                        <div class="layui-input-inline">--}}
                                {{--                                            <input type="text" name="city" autocomplete="off" class="layui-input">--}}
                                {{--                                        </div>--}}
                                {{--                                    </div>--}}
                                {{--                                    <div class="layui-inline">--}}
                                {{--                                        <label class="layui-form-label">关键词职业</label>--}}
                                {{--                                        <div class="layui-input-inline">--}}
                                {{--                                            <input type="text" name="classify" autocomplete="off" class="layui-input">--}}
                                {{--                                        </div>--}}
                                {{--                                    </div>--}}
                                <div class="layui-inline">
                                    <button type="submit" class="layui-btn layui-btn-primary" lay-submit=""
                                            lay-filter="data-search-btn"><i class="layui-icon"></i> 搜 索
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </fieldset>

                <script type="text/html" id="toolbarDemo">
                    <div class="layui-btn-container">
                        <button class="layui-btn layui-btn-sm data-add-btn"> 添加关键词</button>
                        <button class="layui-btn layui-btn-sm layui-btn-danger data-delete-btn"> 删除关键词</button>
                    </div>
                </script>

                <table class="layui-hide" id="currentTableId" lay-filter="currentTableFilter"></table>
                <div class="layui-form layui-border-box layui-table-view" lay-filter="LAY-table-1"
                     lay-id="currentTableId" style=" ">
                    <div class="layui-table-tool">
                        <div class="layui-table-tool-temp">
                            <div class="layui-btn-container">
                                <button class="layui-btn layui-btn-sm data-add-btn"> 添加关键词</button>
                                <button class="layui-btn layui-btn-sm layui-btn-danger data-delete-btn"> 删除关键词</button>
                            </div>
                        </div>

                    </div>
                    <div class="layui-table-box">
                        <div class="layui-table-header">

                        </div>
                        <div class="layui-table-body layui-table-main">
                            <table class="layui-table" cellspacing="0" cellpadding="0" border="0">
                                <tbody>

                                <tr>

                                    <td  >
                                        <div class="layui-table-cell">指令</div>
                                    </td>

                                    <td  >
                                        <div class="layui-table-cell">回复类型</div>
                                    </td> <td  >
                                        <div class="layui-table-cell">官方应用</div>
                                    </td>
                                    <td  >
                                        <div class="layui-table-cell">群聊</div>
                                    </td>
                                    <td  >
                                        <div class="layui-table-cell">私聊</div>
                                    </td>



                                    <th data-field="10" data-key="1-0-10" data-minwidth="50"
                                        class=" layui-table-col-special">
                                        <div class="layui-table-cell laytable-cell-1-0-10" align="center">
                                            <span>操作</span></div>
                                    </th>
                                    <td  >
                                        <div class="layui-table-cell">回复内容</div>
                                    </td>
                                </tr>
                                </tbody>
@foreach($lists as $list)
                                <tr data-index="0" class="">

                                    <td  >
                                        <div class="layui-table-cell">{{$list->key}}</div>
                                    </td>

                                    <td  >
                                        <div class="layui-table-cell">{{$list->type}}</div>
                                    </td> <td  >
                                        <div class="layui-table-cell">{{$list->labe}}</div>
                                    </td>
                                    <td  >
                                        <div class="layui-table-cell">{{$list->pub}}</div>
                                    </td>
                                    <td  >
                                        <div class="layui-table-cell">{{$list->pri}}</div>
                                    </td>


                                    <td data-field="10" data-key="1-0-10" data-content="" data-minwidth="50"
                                        class="layui-table-col-special" align="center">
                                        <div class="layui-table-cell laytable-cell-1-0-10"><a
                                                class="layui-btn layui-btn-xs data-count-edit" lay-event="edit"
                                                one-link-mark="yes">编辑</a> <a
                                                class="layui-btn layui-btn-xs layui-btn-danger data-count-delete"
                                                lay-event="delete" one-link-mark="yes">删除</a></div>
                                    </td>
                                    <td  >
                                        <div class="layui-table-cell">{{$list->content}}</div>
                                    </td>
                                </tr>
@endforeach
                            </table>
                        </div>
                        <div class="layui-table-fixed layui-table-fixed-r layui-hide" style="right: -1px;">
                            <div class="layui-table-header">
                                <table class="layui-table" cellspacing="0" cellpadding="0" border="0">
                                    <thead>
                                    <tr>
                                        <th data-field="10" data-key="1-0-10" data-minwidth="50"
                                            class=" layui-table-col-special">
                                            <div class="layui-table-cell laytable-cell-1-0-10" align="center">
                                                <span>操作</span></div>
                                        </th>
                                    </tr>
                                    </thead>
                                </table>
                                <div class="layui-table-mend"></div>
                            </div>
                            <div class="layui-table-body" style="height: 390px;">
                                <table class="layui-table" cellspacing="0" cellpadding="0" border="0">
                                    <tbody>
                                    <tr data-index="0" class="">
                                        <td data-field="10" data-key="1-0-10" data-content="" data-minwidth="50"
                                            class="layui-table-col-special" align="center">
                                            <div class="layui-table-cell laytable-cell-1-0-10"><a
                                                    class="layui-btn layui-btn-xs data-count-edit" lay-event="edit"
                                                    one-link-mark="yes">编辑</a> <a
                                                    class="layui-btn layui-btn-xs layui-btn-danger data-count-delete"
                                                    lay-event="delete" one-link-mark="yes">删除</a></div>
                                        </td>
                                    </tr>
                                    <tr data-index="1" class="">
                                        <td data-field="10" data-key="1-0-10" data-content="" data-minwidth="50"
                                            class="layui-table-col-special" align="center">
                                            <div class="layui-table-cell laytable-cell-1-0-10"><a
                                                    class="layui-btn layui-btn-xs data-count-edit" lay-event="edit"
                                                    one-link-mark="yes">编辑</a> <a
                                                    class="layui-btn layui-btn-xs layui-btn-danger data-count-delete"
                                                    lay-event="delete" one-link-mark="yes">删除</a></div>
                                        </td>
                                    </tr>
                                    <tr data-index="2" class="">
                                        <td data-field="10" data-key="1-0-10" data-content="" data-minwidth="50"
                                            class="layui-table-col-special" align="center">
                                            <div class="layui-table-cell laytable-cell-1-0-10"><a
                                                    class="layui-btn layui-btn-xs data-count-edit" lay-event="edit"
                                                    one-link-mark="yes">编辑</a> <a
                                                    class="layui-btn layui-btn-xs layui-btn-danger data-count-delete"
                                                    lay-event="delete" one-link-mark="yes">删除</a></div>
                                        </td>
                                    </tr>
                                    <tr data-index="3">
                                        <td data-field="10" data-key="1-0-10" data-content="" data-minwidth="50"
                                            class="layui-table-col-special" align="center">
                                            <div class="layui-table-cell laytable-cell-1-0-10"><a
                                                    class="layui-btn layui-btn-xs data-count-edit" lay-event="edit"
                                                    one-link-mark="yes">编辑</a> <a
                                                    class="layui-btn layui-btn-xs layui-btn-danger data-count-delete"
                                                    lay-event="delete" one-link-mark="yes">删除</a></div>
                                        </td>
                                    </tr>
                                    <tr data-index="4">
                                        <td data-field="10" data-key="1-0-10" data-content="" data-minwidth="50"
                                            class="layui-table-col-special" align="center">
                                            <div class="layui-table-cell laytable-cell-1-0-10"><a
                                                    class="layui-btn layui-btn-xs data-count-edit" lay-event="edit"
                                                    one-link-mark="yes">编辑</a> <a
                                                    class="layui-btn layui-btn-xs layui-btn-danger data-count-delete"
                                                    lay-event="delete" one-link-mark="yes">删除</a></div>
                                        </td>
                                    </tr>
                                    <tr data-index="5">
                                        <td data-field="10" data-key="1-0-10" data-content="" data-minwidth="50"
                                            class="layui-table-col-special" align="center">
                                            <div class="layui-table-cell laytable-cell-1-0-10"><a
                                                    class="layui-btn layui-btn-xs data-count-edit" lay-event="edit"
                                                    one-link-mark="yes">编辑</a> <a
                                                    class="layui-btn layui-btn-xs layui-btn-danger data-count-delete"
                                                    lay-event="delete" one-link-mark="yes">删除</a></div>
                                        </td>
                                    </tr>
                                    <tr data-index="6">
                                        <td data-field="10" data-key="1-0-10" data-content="" data-minwidth="50"
                                            class="layui-table-col-special" align="center">
                                            <div class="layui-table-cell laytable-cell-1-0-10"><a
                                                    class="layui-btn layui-btn-xs data-count-edit" lay-event="edit"
                                                    one-link-mark="yes">编辑</a> <a
                                                    class="layui-btn layui-btn-xs layui-btn-danger data-count-delete"
                                                    lay-event="delete" one-link-mark="yes">删除</a></div>
                                        </td>
                                    </tr>
                                    <tr data-index="7">
                                        <td data-field="10" data-key="1-0-10" data-content="" data-minwidth="50"
                                            class="layui-table-col-special" align="center">
                                            <div class="layui-table-cell laytable-cell-1-0-10"><a
                                                    class="layui-btn layui-btn-xs data-count-edit" lay-event="edit"
                                                    one-link-mark="yes">编辑</a> <a
                                                    class="layui-btn layui-btn-xs layui-btn-danger data-count-delete"
                                                    lay-event="delete" one-link-mark="yes">删除</a></div>
                                        </td>
                                    </tr>
                                    <tr data-index="8">
                                        <td data-field="10" data-key="1-0-10" data-content="" data-minwidth="50"
                                            class="layui-table-col-special" align="center">
                                            <div class="layui-table-cell laytable-cell-1-0-10"><a
                                                    class="layui-btn layui-btn-xs data-count-edit" lay-event="edit"
                                                    one-link-mark="yes">编辑</a> <a
                                                    class="layui-btn layui-btn-xs layui-btn-danger data-count-delete"
                                                    lay-event="delete" one-link-mark="yes">删除</a></div>
                                        </td>
                                    </tr>
                                    <tr data-index="9">
                                        <td data-field="10" data-key="1-0-10" data-content="" data-minwidth="50"
                                            class="layui-table-col-special" align="center">
                                            <div class="layui-table-cell laytable-cell-1-0-10"><a
                                                    class="layui-btn layui-btn-xs data-count-edit" lay-event="edit"
                                                    one-link-mark="yes">编辑</a> <a
                                                    class="layui-btn layui-btn-xs layui-btn-danger data-count-delete"
                                                    lay-event="delete" one-link-mark="yes">删除</a></div>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="layui-table-page">
                        <div id="layui-table-page1">
                            <div class="layui-box layui-laypage layui-laypage-default" id="layui-laypage-1"><a
                                    href="javascript:;" class="layui-laypage-prev layui-disabled" data-page="0"
                                    one-link-mark="yes"><i class="layui-icon"></i></a><span class="layui-laypage-curr"><em
                                        class="layui-laypage-em"></em><em>1</em></span><a href="javascript:;"
                                                                                          data-page="2"
                                                                                          one-link-mark="yes">2</a><a
                                    href="javascript:;" data-page="3" one-link-mark="yes">3</a><span
                                    class="layui-laypage-spr">…</span><a href="javascript:;" class="layui-laypage-last"
                                                                         title="尾页" data-page="67" one-link-mark="yes">67</a><a
                                    href="javascript:;" class="layui-laypage-next" data-page="2" one-link-mark="yes"><i
                                        class="layui-icon"></i></a><span class="layui-laypage-skip">到第<input
                                        type="text" min="1" value="1" class="layui-input">页<button type="button"
                                                                                                   class="layui-laypage-btn">确定</button></span><span
                                    class="layui-laypage-count">共 1000 条</span><span
                                    class="layui-laypage-limits"><select lay-ignore=""><option
                                            value="10">10 条/页</option><option value="15" selected="">15 条/页</option><option
                                            value="20">20 条/页</option><option value="25">25 条/页</option><option
                                            value="50">50 条/页</option><option
                                            value="100">100 条/页</option></select></span></div>
                        </div>
                    </div>
                    <style>.laytable-cell-1-0-0 {
                            width: 50px;
                        }

                        .laytable-cell-1-0-1 {
                            width: 80px;
                        }

                        .laytable-cell-1-0-2 {
                            width: 80px;
                        }

                        .laytable-cell-1-0-3 {
                            width: 80px;
                        }

                        .laytable-cell-1-0-4 {
                            width: 80px;
                        }

                        .laytable-cell-1-0-5 {
                        }

                        .laytable-cell-1-0-6 {
                            width: 80px;
                        }

                        .laytable-cell-1-0-7 {
                            width: 80px;
                        }

                        .laytable-cell-1-0-8 {
                            width: 80px;
                        }

                        .laytable-cell-1-0-9 {
                            width: 135px;
                        }

                        .laytable-cell-1-0-10 {
                        }</style>
                </div>

                <script type="text/html" id="currentTableBar">
                    <a class="layui-btn layui-btn-xs data-count-edit" lay-event="edit">编辑</a>
                    <a class="layui-btn layui-btn-xs layui-btn-danger data-count-delete" lay-event="delete">删除</a>
                </script>

            </div>
        </div>
        {{ $lists->links() }}
        <script>
            layui.use(['form', 'table', 'layuimini', 'element'], function () {
                var $ = layui.jquery,
                    form = layui.form,
                    table = layui.table,
                    layuimini = layui.layuimini;

                table.render({
                    elem: '#currentTableId',
                    url: 'api/table.json',
                    toolbar: '#toolbarDemo',
                    defaultToolbar: ['filter', 'exports', 'print', {
                        title: '提示',
                        layEvent: 'LAYTABLE_TIPS',
                        icon: 'layui-icon-tips'
                    }],
                    cols: [[
                        {type: "checkbox", width: 50, fixed: "left"},
                        {field: 'id', width: 80, title: 'ID', sort: true},
                        {field: 'username', width: 80, title: '关键词名'},
                        {field: 'sex', width: 80, title: '性别', sort: true},
                        {field: 'city', width: 80, title: '城市'},
                        {field: 'sign', title: '签名', minWidth: 150},
                        {field: 'experience', width: 80, title: '积分', sort: true},
                        {field: 'score', width: 80, title: '评分', sort: true},
                        {field: 'classify', width: 80, title: '职业'},
                        {field: 'wealth', width: 135, title: '财富', sort: true},
                        {title: '操作', minWidth: 50, templet: '#currentTableBar', fixed: "right", align: "center"}
                    ]],
                    limits: [10, 15, 20, 25, 50, 100],
                    limit: 15,
                    page: true
                });

                // 监听搜索操作
                form.on('submit(data-search-btn)', function (data) {
                    var result = JSON.stringify(data.field);
                    layer.alert(result, {
                        title: '最终的搜索信息'
                    });

                    //执行搜索重载
                    table.reload('currentTableId', {
                        page: {
                            curr: 1
                        }
                        , where: {
                            searchParams: result
                        }
                    }, 'data');

                    return false;
                });

                // 监听添加操作
                $(".data-add-btn").on("click", function () {

                    var content = layuimini.getHrefContent('page/table/add.html');
                    var openWH = layuimini.getOpenWidthHeight();

                    var index = layer.open({
                        title: '添加关键词',
                        type: 1,
                        shade: 0.2,
                        maxmin: true,
                        shadeClose: true,
                        area: [openWH[0] + 'px', openWH[1] + 'px'],
                        offset: [openWH[2] + 'px', openWH[3] + 'px'],
                        content: content,
                    });
                    $(window).on("resize", function () {
                        layer.full(index);
                    });

                    return false;
                });

                // 监听删除操作
                $(".data-delete-btn").on("click", function () {
                    var checkStatus = table.checkStatus('currentTableId')
                        , data = checkStatus.data;
                    layer.alert(JSON.stringify(data));
                });

                //监听表格复选框选择
                table.on('checkbox(currentTableFilter)', function (obj) {
                    console.log(obj)
                });

                table.on('tool(currentTableFilter)', function (obj) {
                    var data = obj.data;
                    if (obj.event === 'edit') {

                        var content = layuimini.getHrefContent('page/table/add.html');
                        var openWH = layuimini.getOpenWidthHeight();

                        var index = layer.open({
                            title: '编辑关键词',
                            type: 1,
                            shade: 0.2,
                            maxmin: true,
                            shadeClose: true,
                            area: [openWH[0] + 'px', openWH[1] + 'px'],
                            offset: [openWH[2] + 'px', openWH[3] + 'px'],
                            content: content,
                        });
                        $(window).on("resize", function () {
                            layer.full(index);
                        });
                        return false;
                    } else if (obj.event === 'delete') {
                        layer.confirm('真的删除行么', function (index) {
                            obj.del();
                            layer.close(index);
                        });
                    }
                });

            });
        </script>
    </div>


@endsection

