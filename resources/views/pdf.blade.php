<html>
    <head>
        <style>
            .para1 {
              text-align: center;
              font-size: 24px,
            }
            .customers {
  font-family: Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
  margin-bottom: 20px,
}

.customers td, .customers th {
  border: 1px solid #ddd;
  padding: 8px;
}

.customers tr:nth-child(even){background-color: #f2f2f2;}

.customers tr:hover {background-color: #ddd;}

.customers th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #4CAF50;
  color: white;
}
.total{
    text-align: center
}
            </style>
    </head>
    <body>


        <h3 class="para1" >{{$judul}}</h3>
        <h3 class="para1" >{{$periode}}</h3>

        <h4>Data Pemasukan</h4>
        <table class="customers">
            <tr>
              <th>No</th>
              <th>Nama</th>
              <th>Kamar</th>
              <th>Tanggal Transaksi</th>
              <th>Jumlah</th>
            </tr>
            @foreach ($data_pemasukan as $data)
                {{-- <p>This is user {{ $data->id }}</p> --}}
                <tr>
                    <td>{{ $loop->index +1 }}</td>
                    <td>{{ $data->nama_penghuni}}</td>
                    <td>{{ $data->nama_kamar }}</td>
                    <td>{{ $data->hari."-". $data->bulan."-".$data->tahun}}</td>
                    <td>{{ rupiah($data->jumlah) }}</td>

                  </tr>
            @endforeach
            {{-- <tr>
              <td>1</td>
              <td>Alfreds Futterkiste</td>
              <td>Maria Anders</td>
              <td>Germany</td>
            </tr> --}}


              <tr>
                <td colspan="4" class="total"> <b>TOTAL BULAN INI</b></td>
                <td> <b>{{ rupiah($total_pemasukan) }}</b> </td>
              </tr>

          </table>
          {{-- <h4>Data Pengeluaran</h4>
          <table class="customers">
            <tr>
              <th>No</th>
              <th>Judul</th>
              <th>Tanggal Transaksi</th>
              <th>Jumlah</th>
            </tr>
            @foreach ($data_pengeluaran as $data)

                <tr>
                    <td>{{ $loop->index +1 }}</td>
                    <td>{{ $data->judul}}</td>
                    <td>{{ $data->hari."-". $data->bulan."-".$data->tahun}}</td>
                    <td>{{ rupiah($data->jumlah) }}</td>

                  </tr>
            @endforeach


              <tr>
                <td colspan="3" class="total"> <b>TOTAL BULAN INI</b></td>
                <td> <b>{{ rupiah($total_pengeluaran) }}</b> </td>
              </tr>

          </table> --}}
    </body>
</html>
