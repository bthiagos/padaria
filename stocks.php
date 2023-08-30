
<div class="card rounded-0 shadow">
    <div class="card-header d-flex justify-content-between">
        <h3 class="card-title">Lista de Estoque</h3>
        <div class="card-tools align-middle">
            <button class="btn btn-dark btn-sm py-1 rounded-0" type="button" id="create_new">Novo</button>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-hover table-striped table-bordered">
            <colgroup>
                <col width="5%">
                <col width="20%">
                <col width="30%">
                <col width="15%">
                <col width="15%">
                <col width="15%">
            </colgroup>
            <thead>
                <tr>
                    <th class="text-center p-0">#</th>
                    <th class="text-center p-0">Data Adicionada</th>
                    <th class="text-center p-0">Produto</th>
                    <th class="text-center p-0">Quantidade</th>
                    <th class="text-center p-0">Validade</th>
                    <th class="text-center p-0">Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $sql = "SELECT s.*,p.name as pname,p.product_code FROM `stock_list` s inner join `product_list` p on s.product_id = p.product_id where p.delete_flag = 0 order by unix_timestamp(s.date_added) desc";
                $qry = $conn->query($sql);
                $i = 1;
                    while($row = $qry->fetch_assoc()):
                ?>
                <tr class="<?php echo strtotime(date("Y-m-d")) > strtotime($row['expiry_date']) ? 'bg-danger bg-opacity-50' : '' ?>">
                    <td class="text-center p-0"><?php echo $i++; ?></td>
                    <td class="py-0 px-1"><?php echo date("Y-m-d",strtotime($row['date_added'])) ?></td>
                    <td class="py-0 px-1">
                        <div class="fs-6 fw-bold truncate-1" title="<?php echo $row['product_code'] ?>"><?php echo $row['product_code'] ?></div>
                        <div class="fs-6 fw-light truncate-1" title="<?php echo $row['pname'] ?>"><?php echo $row['pname'] ?></div>
                    </td>
                    <td class="py-0 px-1 text-end"><?php echo format_num($row['quantity']) ?></td>
                    <td class="py-0 px-1 text-center"><?php echo date("Y-m-d",strtotime($row['expiry_date'])) ?></td>
                    <td class="text-center py-0 px-1">
                        <div class="btn-group" role="group">
                            <button id="btnGroupDrop1" type="button" class="btn btn-primary dropdown-toggle btn-sm rounded-0 py-0" data-bs-toggle="dropdown" aria-expanded="false">
                            Action
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                            <li><a class="dropdown-item edit_data" data-id = '<?php echo $row['stock_id'] ?>' href="javascript:void(0)">Edit</a></li>
                            <li><a class="dropdown-item delete_data" data-id = '<?php echo $row['stock_id'] ?>' data-name = '<?php echo $row['product_code']." - ".$row['pname'] ?>' href="javascript:void(0)">Delete</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
               
            </tbody>
        </table>
    </div>
</div>
<script>
    $(function(){
        $('#create_new').click(function(){
            uni_modal('Adicionar Novo Estoque',"manage_stock.php")
        })
        $('.edit_data').click(function(){
            uni_modal('Editar Estoque',"manage_stock.php?id="+$(this).attr('data-id'))
        })
        $('.view_data').click(function(){
            uni_modal('Visualizar Estoque',"view_stock.php?id="+$(this).attr('data-id'),'')
        })
        $('.delete_data').click(function(){
            _conf("Are you sure to delete <b>"+$(this).attr('data-name')+"</b> from Stock List?",'delete_data',[$(this).attr('data-id')])
        })
        $('table td,table th').addClass('align-middle')
        $('table').dataTable({
            "language": {
            "url": "//cdn.datatables.net/plug-ins/1.12.1/i18n/pt-BR.json"
        },
            columnDefs: [
                { orderable: false, targets:3 }
            ]
        })
    })
    function delete_data($id){
        $('#confirm_modal button').attr('disabled',true)
        $.ajax({
            url:'./Actions.php?a=delete_stock',
            method:'POST',
            data:{id:$id},
            dataType:'JSON',
            error:err=>{
                console.log(err)
                alert("Ocorreu um erro.")
                $('#confirm_modal button').attr('disabled',false)
            },
            success:function(resp){
                if(resp.status == 'success'){
                    location.reload()
                }else{
                    alert("Ocorreu um erro.")
                    $('#confirm_modal button').attr('disabled',false)
                }
            }
        })
    }
</script>