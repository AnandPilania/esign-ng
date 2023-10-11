<?php

namespace Core\Helpers;

class ExcelHelper
{
    const CELL = ['A', 'B', 'C', 'D', 'E', 'F','G', 'H', 'I', 'J', 'K', 'L','M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
    public function setCell($sheet, $startCell = 'A', $row, $numberCell, $cellData)
    {
        // length cell
        // lưu ý helper này chỉ hỗ trợ sheets nhỏ hơn 676 cột :))
        $i = 0; $a = 0; $b = 1; $cell = 0; // đối số
        if(strlen($startCell) == 1){
            $i = array_search($startCell, self::CELL);
        } else {
            $a = array_search(substr($startCell, 0 ,1), self::CELL) + 1;
            $i = array_search(substr($startCell, 1 ,1), self::CELL);
        }
        foreach ($cellData as $c){
            if ($a == 0) {
                $sheet->setCellValue(self::CELL[$i] . $row, $c);
            } else {
                $sheet->setCellValue(self::CELL[$a - 1] . self::CELL[$i] . $row, $c);
            }
            $i++;
            $cell++;
            if (floor($cell / 26) == $b){
                $a++;
                $b++;
                $i = 0;
            }
        }
    }
}
