<?php

/******************************************
 Asisten Pemrogaman 13 & 14
******************************************/

include("KontrakView.php");
include("presenter/ProsesMahasiswa.php");

class TampilMahasiswa implements KontrakView
{
	private $prosesmahasiswa; // Presenter yang dapat berinteraksi langsung dengan view
	private $tpl;

	function __construct()
	{
		//konstruktor
		$this->prosesmahasiswa = new ProsesMahasiswa();
	}

	function tampil()
	{
		// Process form submissions
		$this->processForm();
		
		$this->prosesmahasiswa->prosesDataMahasiswa();
		$data = null;

		//semua terkait tampilan adalah tanggung jawab view
		for ($i = 0; $i < $this->prosesmahasiswa->getSize(); $i++) {
			$no = $i + 1;
			$gender = $this->prosesmahasiswa->getGender($i);
			$genderDisplay = $gender == 'Laki-laki' ? 
				'<span class="badge badge-gender badge-male"><i class="fas fa-mars me-1"></i>Laki-laki</span>' : 
				'<span class="badge badge-gender badge-female"><i class="fas fa-venus me-1"></i>Perempuan</span>';
			
			// Format tanggal lahir
			$tl = date('d M Y', strtotime($this->prosesmahasiswa->getTl($i)));
			
			$data .= "<tr>
			<td class='text-center'>" . $no . "</td>
			<td>" . $this->prosesmahasiswa->getNim($i) . "</td>
			<td><strong>" . $this->prosesmahasiswa->getNama($i) . "</strong></td>
			<td>" . $this->prosesmahasiswa->getTempat($i) . "</td>
			<td>" . $tl . "</td>
			<td class='text-center'>" . $genderDisplay . "</td>
			<td><a href='mailto:" . $this->prosesmahasiswa->getEmail($i) . "'>" . $this->prosesmahasiswa->getEmail($i) . "</a></td>
			<td><a href='tel:" . $this->prosesmahasiswa->gettelp($i) . "'>" . $this->prosesmahasiswa->gettelp($i) . "</a></td>
			<td class='action-buttons'>
				<a href='index.php?action=edit&id=" . $this->prosesmahasiswa->getId($i) . "' class='btn btn-warning btn-sm'>
					<i class='fas fa-edit'></i> Edit
				</a>
				<a href='index.php?action=delete&id=" . $this->prosesmahasiswa->getId($i) . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this student?\")'>
					<i class='fas fa-trash'></i> Delete
				</a>
			</td>
			</tr>";
		}
		
		// Membaca template skin.html
		$this->tpl = new Template("templates/skin.html");

		// Mengganti kode Data_Tabel dengan data yang sudah diproses
		$this->tpl->replace("DATA_TABEL", $data);
		
		// Mengganti kode DATA_FORM dengan form yang sesuai (add atau edit)
		$form = $this->getForm();
		$this->tpl->replace("DATA_FORM", $form);

		// Menampilkan ke layar
		$this->tpl->write();
	}
	
	private function processForm()
	{
		// Handle form submissions
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if (isset($_POST['add'])) {
				// Add new student
				$nim = $_POST['nim'];
				$nama = $_POST['nama'];
				$tempat = $_POST['tempat'];
				$tl = $_POST['tl'];
				$gender = $_POST['gender'];
				$email = $_POST['email'];
				$telp = $_POST['telp'];
				
				$this->prosesmahasiswa->addDataMahasiswa($nim, $nama, $tempat, $tl, $gender, $email, $telp);
				header("Location: index.php");
				exit;
			} elseif (isset($_POST['update'])) {
				// Update student
				$id = $_POST['id'];
				$nim = $_POST['nim'];
				$nama = $_POST['nama'];
				$tempat = $_POST['tempat'];
				$tl = $_POST['tl'];
				$gender = $_POST['gender'];
				$email = $_POST['email'];
				$telp = $_POST['telp'];
				
				$this->prosesmahasiswa->updateDataMahasiswa($id, $nim, $nama, $tempat, $tl, $gender, $email, $telp);
				header("Location: index.php");
				exit;
			}
		}
		
		// Handle delete action
		if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
			$id = $_GET['id'];
			$this->prosesmahasiswa->deleteDataMahasiswa($id);
			header("Location: index.php");
			exit;
		}
	}
	
	private function getForm()
	{
		$action = isset($_GET['action']) ? $_GET['action'] : 'add';
		$id = isset($_GET['id']) ? $_GET['id'] : '';
		
		$nim = '';
		$nama = '';
		$tempat = '';
		$tl = '';
		$gender = '';
		$email = '';
		$telp = '';
		$button = 'Add';
		$formTitle = 'Add New Student';
		$formIcon = 'fa-plus-circle';
		$hiddenInput = '';
		
		if ($action == 'edit' && !empty($id)) {
			$mahasiswa = $this->prosesmahasiswa->getMahasiswaById($id);
			if ($mahasiswa) {
				$nim = $mahasiswa['nim'];
				$nama = $mahasiswa['nama'];
				$tempat = $mahasiswa['tempat'];
				$tl = $mahasiswa['tl'];
				$gender = $mahasiswa['gender'];
				$email = $mahasiswa['email'];
				$telp = $mahasiswa['telp'];
				$button = 'Update';
				$formTitle = 'Edit Student';
				$formIcon = 'fa-edit';
				$hiddenInput = "<input type='hidden' name='id' value='$id'>";
			}
		}
		
		$form = "
		<div class='card mb-4'>
			<div class='card-header'>
				<h5 class='card-title'><i class='fas $formIcon me-2'></i>$formTitle</h5>
			</div>
			<div class='card-body'>
				<form method='post' action='index.php'>
					$hiddenInput
					<div class='row mb-3'>
						<div class='col-md-6'>
							<label for='nim' class='form-label'><i class='fas fa-id-card me-1'></i> NIM</label>
							<input type='text' class='form-control' id='nim' name='nim' value='$nim' required>
						</div>
						<div class='col-md-6'>
							<label for='nama' class='form-label'><i class='fas fa-user me-1'></i> Nama</label>
							<input type='text' class='form-control' id='nama' name='nama' value='$nama' required>
						</div>
					</div>
					<div class='row mb-3'>
						<div class='col-md-6'>
							<label for='tempat' class='form-label'><i class='fas fa-map-marker-alt me-1'></i> Tempat Lahir</label>
							<input type='text' class='form-control' id='tempat' name='tempat' value='$tempat' required>
						</div>
						<div class='col-md-6'>
							<label for='tl' class='form-label'><i class='fas fa-calendar-alt me-1'></i> Tanggal Lahir</label>
							<input type='date' class='form-control' id='tl' name='tl' value='$tl' required>
						</div>
					</div>
					<div class='row mb-3'>
						<div class='col-md-4'>
							<label for='gender' class='form-label'><i class='fas fa-venus-mars me-1'></i> Gender</label>
							<select class='form-select' id='gender' name='gender' required>
								<option value=''>Select Gender</option>
								<option value='Laki-laki' " . ($gender == 'Laki-laki' ? 'selected' : '') . ">Laki-laki</option>
								<option value='Perempuan' " . ($gender == 'Perempuan' ? 'selected' : '') . ">Perempuan</option>
							</select>
						</div>
						<div class='col-md-4'>
							<label for='email' class='form-label'><i class='fas fa-envelope me-1'></i> Email</label>
							<input type='email' class='form-control' id='email' name='email' value='$email' required>
						</div>
						<div class='col-md-4'>
							<label for='telp' class='form-label'><i class='fas fa-phone me-1'></i> telp</label>
							<input type='text' class='form-control' id='telp' name='telp' value='$telp' required>
						</div>
					</div>
					<div class='text-end'>
						<a href='index.php' class='btn btn-secondary me-2'>
							<i class='fas fa-times me-1'></i> Cancel
						</a>
						<button type='submit' name='" . strtolower($button) . "' class='btn btn-primary'>
							<i class='fas fa-" . (strtolower($button) == 'add' ? 'save' : 'sync') . " me-1'></i> $button
						</button>
					</div>
				</form>
			</div>
		</div>";
		
		return $form;
	}
}