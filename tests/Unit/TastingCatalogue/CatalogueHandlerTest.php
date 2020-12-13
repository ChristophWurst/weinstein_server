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
 */

namespace Test\Unit\TastingCatalogue;

use App\Database\Repositories\CompetitionRepository;
use App\Database\Repositories\WineRepository;
use App\Exceptions\InvalidCompetitionStateException;
use App\Exceptions\ValidationException;
use App\MasterData\Competition;
use App\MasterData\CompetitionState;
use App\TastingCatalogue\CatalogueHandler;
use App\Wine;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\UploadedFile;
use Mockery;
use Mockery\MockInterface;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Test\TestCase;

class CatalogueHandlerTest extends TestCase
{
    /** @var DatabaseManager|MockInterface */
    private $db;

    /** @var WineRepository|MockInterface */
    private $wineRepo;

    /** @var CompetitionRepository|MockInterface */
    private $competitionRepo;

    /** @var CatalogueHandler|MockInterface */
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = Mockery::mock(DatabaseManager::class);
        $this->wineRepo = Mockery::mock(WineRepository::class);
        $this->competitionRepo = Mockery::mock(CompetitionRepository::class);

        $this->handler = Mockery::mock(CatalogueHandler::class,
                [
                $this->db,
                $this->wineRepo,
                $this->competitionRepo,
            ])
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
    }

    /**
     * Don't actually import anything, but simulate that one unassigned wine
     * will be left -> rollback & validation exception.
     */
    public function testImportCatalogueNumbersWithIncompleteData()
    {
        $file = Mockery::mock(UploadedFile::class);
        $competition = Mockery::mock(Competition::class);
        $competitionState = Mockery::mock(CompetitionState::class);
        $doc = $this->getMockBuilder(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)->disableOriginalConstructor()->getMock();
        $sheet = $this->getMockBuilder(Worksheet::class)->disableOriginalConstructor()->getMock();
        $dbConnection = Mockery::mock(Connection::class);
        $competition->shouldReceive('getAttribute')
            ->with('competitionState')
            ->andReturn($competitionState);
        $competitionState->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(CompetitionState::STATE_CATALOGUE_NUMBERS);
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
        $sheet->expects($this->once())
            ->method('toArray')
            ->willReturn([]);
        $this->wineRepo->shouldReceive('getNumberOfWinesWithoutCatalogueNumber')
            ->once()
            ->andReturn(1);
        $dbConnection->shouldReceive('rollBack')
            ->once();
        $this->expectException(ValidationException::class);

        $this->handler->importCatalogueNumbers($file, $competition);
    }

    public function testImportWrongCompetitionState()
    {
        $file = Mockery::mock(UploadedFile::class);
        $competition = Mockery::mock(Competition::class);
        $competitionState = Mockery::mock(CompetitionState::class);
        $competition->shouldReceive('getAttribute')
            ->with('competitionState')
            ->andReturn($competitionState);
        $competitionState->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(CompetitionState::STATE_ENROLLMENT);
        $this->expectException(InvalidCompetitionStateException::class);

        $this->handler->importCatalogueNumbers($file, $competition);
    }

    public function testImportNoCatalogueNumbers()
    {
        $file = Mockery::mock(UploadedFile::class);
        $competition = Mockery::mock(Competition::class);
        $competitionState = Mockery::mock(CompetitionState::class);
        $doc = $this->getMockBuilder(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)->disableOriginalConstructor()->getMock();
        $sheet = $this->getMockBuilder(Worksheet::class)->disableOriginalConstructor()->getMock();
        $dbConnection = Mockery::mock(Connection::class);
        $competition->shouldReceive('getAttribute')
            ->with('competitionState')
            ->andReturn($competitionState);
        $competitionState->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(CompetitionState::STATE_CATALOGUE_NUMBERS);
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
        $sheet->expects($this->once())
            ->method('toArray')
            ->willReturn([]);
        $this->wineRepo->shouldReceive('getNumberOfWinesWithoutCatalogueNumber')
            ->once()
            ->andReturn(0);
        $dbConnection->shouldReceive('commit')
            ->once();

        $importedRows = $this->handler->importCatalogueNumbers($file, $competition);

        $this->assertSame(0, $importedRows);
    }

    public function testImportCatalogueNumbersWithNonExistingWine()
    {
        $file = Mockery::mock(UploadedFile::class);
        $competition = Mockery::mock(Competition::class);
        $competitionState = Mockery::mock(CompetitionState::class);
        $doc = $this->getMockBuilder(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)->disableOriginalConstructor()->getMock();
        $sheet = $this->getMockBuilder(Worksheet::class)->disableOriginalConstructor()->getMock();
        $wine1 = Mockery::mock(Wine::class);
        $competition->shouldReceive('getAttribute')
            ->with('competitionState')
            ->andReturn($competitionState);
        $competitionState->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(CompetitionState::STATE_CATALOGUE_NUMBERS);
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
        $sheet->expects($this->once())
            ->method('toArray')
            ->willReturn([
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
        $this->expectException(ValidationException::class);

        $this->handler->importCatalogueNumbers($file, $competition);
    }

    public function testImportCatalogueNumbersWithNonChosenWine()
    {
        $file = Mockery::mock(UploadedFile::class);
        $competition = Mockery::mock(Competition::class);
        $competitionState = Mockery::mock(CompetitionState::class);
        $doc = $this->getMockBuilder(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)->disableOriginalConstructor()->getMock();
        $sheet = $this->getMockBuilder(Worksheet::class)->disableOriginalConstructor()->getMock();
        $wine1 = Mockery::mock(Wine::class);
        $competition->shouldReceive('getAttribute')
            ->with('competitionState')
            ->andReturn($competitionState);
        $competitionState->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(CompetitionState::STATE_CATALOGUE_NUMBERS);
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
        $sheet->expects($this->once())
            ->method('toArray')
            ->willReturn([
                [100, 1],
                [101, 2],
        ]);
        $this->wineRepo->shouldReceive('findByNr')
            ->with($competition, 100)
            ->andReturn($wine1);
        $wine1->shouldReceive('getAttribute')
            ->with('chosen')
            ->andReturn(false);
        $this->wineRepo->shouldReceive('update')
            ->never()
            ->with($wine1, ['catalogue_number' => 1]);
        $dbConnection->shouldReceive('rollBack')
            ->once();
        $this->expectException(ValidationException::class);

        $this->handler->importCatalogueNumbers($file, $competition);
    }

    /**
     * Simulate only one column is given -> import is invalid.
     */
    public function testImportCatalogueNumbersWithIncompleteSpreadSheetData()
    {
        $file = Mockery::mock(UploadedFile::class);
        $competition = Mockery::mock(Competition::class);
        $competitionState = Mockery::mock(CompetitionState::class);
        $doc = $this->getMockBuilder(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)->disableOriginalConstructor()->getMock();
        $sheet = $this->getMockBuilder(Worksheet::class)->disableOriginalConstructor()->getMock();
        $wine1 = Mockery::mock(Wine::class);
        $competition->shouldReceive('getAttribute')
            ->with('competitionState')
            ->andReturn($competitionState);
        $competitionState->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(CompetitionState::STATE_CATALOGUE_NUMBERS);
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
        $sheet->expects($this->once())
            ->method('toArray')
            ->willReturn([
                [100],
                [101],
        ]);
        $this->wineRepo->shouldReceive('update')
            ->never()
            ->with($wine1, ['catalogue_number' => 1]);
        $dbConnection->shouldReceive('rollBack')
            ->once();
        $this->expectException(ValidationException::class);

        $this->handler->importCatalogueNumbers($file, $competition);
    }

    public function testImportCatalogueNumbers()
    {
        $file = Mockery::mock(UploadedFile::class);
        $competition = Mockery::mock(Competition::class);
        $competitionState = Mockery::mock(CompetitionState::class);
        $doc = $this->createMock(\PhpOffice\PhpSpreadsheet\Spreadsheet::class);
        $sheet = $this->getMockBuilder(Worksheet::class)->disableOriginalConstructor()->getMock();
        $wine1 = Mockery::mock(Wine::class);
        $wine2 = Mockery::mock(Wine::class);
        $competition->shouldReceive('getAttribute')
            ->with('competitionState')
            ->andReturn($competitionState);
        $competitionState->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(CompetitionState::STATE_CATALOGUE_NUMBERS);
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
        $sheet->expects($this->once())
            ->method('toArray')
            ->willReturn([
                [100, 1],
                [101, 2],
        ]);
        $this->wineRepo->shouldReceive('findByNr')
            ->with($competition, 100)
            ->andReturn($wine1);
        $wine1->shouldReceive('getAttribute')
            ->with('chosen')
            ->andReturn(true);
        $this->wineRepo->shouldReceive('update')
            ->once()
            ->with($wine1, ['catalogue_number' => 1]);
        $this->wineRepo->shouldReceive('findByNr')
            ->with($competition, 101)
            ->andReturn($wine2);
        $wine2->shouldReceive('getAttribute')
            ->with('chosen')
            ->andReturn(true);
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

    public function testImportCatalogueNumbersWithRealFile()
    {
        $file = Mockery::mock(UploadedFile::class);
        $competition = Mockery::mock(Competition::class);
        $competitionState = Mockery::mock(CompetitionState::class);
        $wine1 = Mockery::mock(Wine::class);
        $competition->shouldReceive('getAttribute')
            ->with('competitionState')
            ->andReturn($competitionState);
        $competitionState->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(CompetitionState::STATE_CATALOGUE_NUMBERS);
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
            ->andReturn(dirname(__FILE__).'/../data/import_catalogue_number_01.ods');
        $this->wineRepo->shouldReceive('findByNr')
            ->with($competition, 100)
            ->andReturn($wine1);
        $wine1->shouldReceive('getAttribute')
            ->with('chosen')
            ->andReturn(true);
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

    public function testImportCatalogueNumbersWithInvalidFile()
    {
        $file = Mockery::mock(UploadedFile::class);
        $competition = Mockery::mock(Competition::class);
        $competitionState = Mockery::mock(CompetitionState::class);
        $wine1 = Mockery::mock(Wine::class);
        $competition->shouldReceive('getAttribute')
            ->with('competitionState')
            ->andReturn($competitionState);
        $competitionState->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(CompetitionState::STATE_CATALOGUE_NUMBERS);
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
        $this->expectException(ValidationException::class);

        $this->handler->importCatalogueNumbers($file, $competition);
    }
}
