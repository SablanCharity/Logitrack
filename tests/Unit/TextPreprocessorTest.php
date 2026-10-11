<?php

namespace Tests\Unit;

use App\Services\TextPreprocessor;
use Tests\TestCase;

class TextPreprocessorTest extends TestCase
{
    private TextPreprocessor $preprocessor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->preprocessor = new TextPreprocessor();
    }

    public function test_normalizes_text_to_lowercase(): void
    {
        $result = $this->preprocessor->normalize(
            'STUDENT ATTENDANCE Monitoring SYSTEM'
        );

        $this->assertSame(
            'student attendance monitoring system',
            $result
        );
    }

    public function test_tokenizes_text_into_words(): void
    {
        $result = $this->preprocessor->tokenize(
            'Student attendance monitoring system'
        );

        $this->assertIsArray($result);
        $this->assertContains('student', $result);
        $this->assertContains('attendance', $result);
        $this->assertContains('monitoring', $result);
    }

    public function test_removes_stop_words(): void
    {
        $tokens = [
            'the',
            'student',
            'is',
            'monitoring',
            'the',
            'classroom',
        ];

        $result = $this->preprocessor->removeStopWords($tokens);

        $this->assertIsArray($result);
        $this->assertContains('student', $result);
        $this->assertContains('monitoring', $result);
        $this->assertContains('classroom', $result);
        $this->assertNotContains('the', $result);
    }

    public function test_process_returns_processed_tokens(): void
    {
        $result = $this->preprocessor->process(
            'The student is monitoring classroom attendance.'
        );

        $this->assertIsArray($result);
        $this->assertContains('student', $result);
        $this->assertContains('attendance', $result);
        $this->assertNotContains('the', $result);
    }

    public function test_handles_empty_input(): void
    {
        $result = $this->preprocessor->process('');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}