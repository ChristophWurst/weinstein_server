<?php

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License,version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace Test\Unit\TastingCatalogue;

use App\Database\Repositories\WineRepository;
use App\Exceptions\ValidationException;
use App\MasterData\Competition;
use App\TastingCatalogue\CatalogueHandler;
use App\Wine;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\UploadedFile;
use Mockery;
use PHPExcel;
use PHPExcel_Worksheet;
use Test\TestCase;

class CatalogueHandlerTest extends TestCase {

	/** @var DatabaseManager|Mockery\MockInterface */
	private $db;

	/** @var WineRepository|Mockery\MockInterface */
	private $wineRepo;

	/** @var CatalogueHandler|Mockery\MockInterface */
	private $handler;

	protected function setUp() {
		parent::setUp();

		$this->db = Mockery::mock(DatabaseManager::class);
		$this->wineRepo = Mockery::mock(WineRepository::class);

		$this->handler = Mockery::mock(CatalogueHandler::class, [
				$this->db,
				$this->wineRepo,
			])
			->shouldAllowMockingProtectedMethods()
			->makePartial();
	}

	/**
	 * Don't actually import anything, but simulate that one unassigned wine
	 * will be left -> rollback & validation exception
	 */
	public function testImportCatalogueNumbersWithIncompleteData() {
		$file = Mockery::mock(UploadedFile::class);
		$competition = Mockery::mock(Competition::class);
		$doc = $this->getMockBuilder(PHPExcel::class)->disableOriginalConstructor()->getMock();
		$sheet = Mockery::mock(PHPExcel_Worksheet::class);
		$dbConnection = Mockery::mock(Connection::class);
		$this->db->shouldReceive('connection')
			->once()
			->andReturn($dbConnection);
		$dbConnection->shouldReceive('beginTransaction')
			->once();
		$this->wineRepo->shouldReceive('resetCatalogueNumbers')
			->once()
			->with($competition);
		$this->handler->shouldReceive('loadExcelFile')
			->once()
			->with($file)
			->andReturn($doc);
		$doc->expects($this->once())
			->method('getActiveSheet')
			->willReturn($sheet);
		$sheet->shouldReceive('toArray')
			->once()
			->andReturn([]);
		$this->wineRepo->shouldReceive('getNumberOfWinesWithoutCatalogueNumber')
			->once()
			->andReturn(1);
		$dbConnection->shouldReceive('rollBack')
			->once();
		$this->setExpectedException(ValidationException::class);

		$this->handler->importCatalogueNumbers($file, $competition);
	}

	public function testImportNoCatalogueNumbers() {
		$file = Mockery::mock(UploadedFile::class);
		$competition = Mockery::mock(Competition::class);
		$doc = $this->getMockBuilder(PHPExcel::class)->disableOriginalConstructor()->getMock();
		$sheet = Mockery::mock(PHPExcel_Worksheet::class);
		$dbConnection = Mockery::mock(Connection::class);
		$this->db->shouldReceive('connection')
			->once()
			->andReturn($dbConnection);
		$dbConnection->shouldReceive('beginTransaction')
			->once();
		$this->wineRepo->shouldReceive('resetCatalogueNumbers')
			->once()
			->with($competition);
		$this->handler->shouldReceive('loadExcelFile')
			->once()
			->with($file)
			->andReturn($doc);
		$doc->expects($this->once())
			->method('getActiveSheet')
			->willReturn($sheet);
		$sheet->shouldReceive('toArray')
			->once()
			->andReturn([]);
		$this->wineRepo->shouldReceive('getNumberOfWinesWithoutCatalogueNumber')
			->once()
			->andReturn(0);
		$dbConnection->shouldReceive('commit')
			->once();

		$importedRows = $this->handler->importCatalogueNumbers($file, $competition);

		$this->assertSame(0, $importedRows);
	}

	public function testImportCatalogueNumbersWithNonExistingWine() {
		$file = Mockery::mock(UploadedFile::class);
		$competition = Mockery::mock(Competition::class);
		$doc = $this->getMockBuilder(PHPExcel::class)->disableOriginalConstructor()->getMock();
		$sheet = Mockery::mock(PHPExcel_Worksheet::class);
		$wine1 = Mockery::mock(Wine::class);
		$dbConnection = Mockery::mock(Connection::class);
		$this->db->shouldReceive('connection')
			->once()
			->andReturn($dbConnection);
		$dbConnection->shouldReceive('beginTransaction')
			->once();
		$this->wineRepo->shouldReceive('resetCatalogueNumbers')
			->once()
			->with($competition);
		$this->handler->shouldReceive('loadExcelFile')
			->once()
			->with($file)
			->andReturn($doc);
		$doc->expects($this->once())
			->method('getActiveSheet')
			->willReturn($sheet);
		$sheet->shouldReceive('toArray')
			->once()
			->andReturn([
				[100, 1],
				[101, 2],
		]);
		$this->wineRepo->shouldReceive('findByNr')
			->with($competition, 100)
			->andReturn(null);
		$this->wineRepo->shouldReceive('update')
			->never()
			->with($wine1, ['catalogue_number' => 1]);
		$dbConnection->shouldReceive('rollBack')
			->once();
		$this->setExpectedException(ValidationException::class);

		$this->handler->importCatalogueNumbers($file, $competition);
	}

	/**
	 * Simulate only one column is given -> import is invalid
	 */
	public function testImportCatalogueNumbersWithIncompleteSpreadSheetData() {
		$file = Mockery::mock(UploadedFile::class);
		$competition = Mockery::mock(Competition::class);
		$doc = $this->getMockBuilder(PHPExcel::class)->disableOriginalConstructor()->getMock();
		$sheet = Mockery::mock(PHPExcel_Worksheet::class);
		$wine1 = Mockery::mock(Wine::class);
		$dbConnection = Mockery::mock(Connection::class);
		$this->db->shouldReceive('connection')
			->once()
			->andReturn($dbConnection);
		$dbConnection->shouldReceive('beginTransaction')
			->once();
		$this->wineRepo->shouldReceive('resetCatalogueNumbers')
			->once()
			->with($competition);
		$this->handler->shouldReceive('loadExcelFile')
			->once()
			->with($file)
			->andReturn($doc);
		$doc->expects($this->once())
			->method('getActiveSheet')
			->willReturn($sheet);
		$sheet->shouldReceive('toArray')
			->once()
			->andReturn([
				[100],
				[101],
		]);
		$this->wineRepo->shouldReceive('update')
			->never()
			->with($wine1, ['catalogue_number' => 1]);
		$dbConnection->shouldReceive('rollBack')
			->once();
		$this->setExpectedException(ValidationException::class);

		$this->handler->importCatalogueNumbers($file, $competition);
	}

	public function testImportCatalogueNumbers() {
		$file = Mockery::mock(UploadedFile::class);
		$competition = Mockery::mock(Competition::class);
		$doc = $this->getMockBuilder(PHPExcel::class)->disableOriginalConstructor()->getMock();
		$sheet = Mockery::mock(PHPExcel_Worksheet::class);
		$wine1 = Mockery::mock(Wine::class);
		$wine2 = Mockery::mock(Wine::class);
		$dbConnection = Mockery::mock(Connection::class);
		$this->db->shouldReceive('connection')
			->once()
			->andReturn($dbConnection);
		$dbConnection->shouldReceive('beginTransaction')
			->once();
		$this->wineRepo->shouldReceive('resetCatalogueNumbers')
			->once()
			->with($competition);
		$this->handler->shouldReceive('loadExcelFile')
			->once()
			->with($file)
			->andReturn($doc);
		$doc->expects($this->once())
			->method('getActiveSheet')
			->willReturn($sheet);
		$sheet->shouldReceive('toArray')
			->once()
			->andReturn([
				[100, 1],
				[101, 2],
		]);
		$this->wineRepo->shouldReceive('findByNr')
			->with($competition, 100)
			->andReturn($wine1);
		$this->wineRepo->shouldReceive('update')
			->once()
			->with($wine1, ['catalogue_number' => 1]);
		$this->wineRepo->shouldReceive('findByNr')
			->with($competition, 101)
			->andReturn($wine2);
		$this->wineRepo->shouldReceive('update')
			->once()
			->with($wine2, ['catalogue_number' => 2]);
		$this->wineRepo->shouldReceive('getNumberOfWinesWithoutCatalogueNumber')
			->once()
			->andReturn(0);
		$dbConnection->shouldReceive('commit')
			->once();

		$importedRows = $this->handler->importCatalogueNumbers($file, $competition);

		$this->assertSame(2, $importedRows);
	}

	public function testImportCatalogueNumbersWithRealFile() {
		$file = Mockery::mock(UploadedFile::class);
		$competition = Mockery::mock(Competition::class);
		$wine1 = Mockery::mock(Wine::class);
		$dbConnection = Mockery::mock(Connection::class);
		$this->db->shouldReceive('connection')
			->once()
			->andReturn($dbConnection);
		$dbConnection->shouldReceive('beginTransaction')
			->once();
		$this->wineRepo->shouldReceive('resetCatalogueNumbers')
			->once()
			->with($competition);
		$file->shouldReceive('getRealPath')
			->once()
			->andReturn(dirname(__FILE__) . '/../data/import_catalogue_number_01.ods');
		$this->wineRepo->shouldReceive('findByNr')
			->with($competition, 100)
			->andReturn($wine1);
		$this->wineRepo->shouldReceive('update')
			->once()
			->with($wine1, ['catalogue_number' => 1]);
		$this->wineRepo->shouldReceive('getNumberOfWinesWithoutCatalogueNumber')
			->once()
			->andReturn(0);
		$dbConnection->shouldReceive('commit')
			->once();

		$importedRows = $this->handler->importCatalogueNumbers($file, $competition);

		$this->assertSame(1, $importedRows);
	}

	public function testImportCatalogueNumbersWithInvalidFile() {
		$file = Mockery::mock(UploadedFile::class);
		$competition = Mockery::mock(Competition::class);
		$wine1 = Mockery::mock(Wine::class);
		$dbConnection = Mockery::mock(Connection::class);
		$this->db->shouldReceive('connection')
			->once()
			->andReturn($dbConnection);
		$dbConnection->shouldReceive('beginTransaction')
			->once();
		$this->wineRepo->shouldReceive('resetCatalogueNumbers')
			->once()
			->with($competition);
		$file->shouldReceive('getRealPath')
			->once()
			->andReturn('file_does_not_exist.ods');
		$this->wineRepo->shouldReceive('findByNr')
			->with($competition, 100)
			->andReturn($wine1);
		$dbConnection->shouldReceive('rollBack')
			->once();
		$this->setExpectedException(ValidationException::class);

		$this->handler->importCatalogueNumbers($file, $competition);
	}

}
