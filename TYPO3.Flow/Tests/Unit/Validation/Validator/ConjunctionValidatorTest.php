<?php
namespace TYPO3\Flow\Tests\Unit\Validation\Validator;

/*
 * This file is part of the TYPO3.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

/**
 * Testcase for the Conjunction Validator
 *
 */
class ConjunctionValidatorTest extends \TYPO3\Flow\Tests\UnitTestCase
{
    /**
     * @test
     */
    public function addingValidatorsToAJunctionValidatorWorks()
    {
        $conjunctionValidator = $this->getAccessibleMock(\TYPO3\Flow\Validation\Validator\ConjunctionValidator::class, array('dummy'), array(array()));

        $mockValidator = $this->createMock(\TYPO3\Flow\Validation\Validator\ValidatorInterface::class);
        $conjunctionValidator->addValidator($mockValidator);
        $this->assertTrue($conjunctionValidator->_get('validators')->contains($mockValidator));
    }

    /**
     * @test
     */
    public function allValidatorsInTheConjunctionAreCalledEvenIfOneReturnsError()
    {
        $validatorConjunction = new \TYPO3\Flow\Validation\Validator\ConjunctionValidator(array());
        $validatorObject = $this->createMock(\TYPO3\Flow\Validation\Validator\ValidatorInterface::class);
        $validatorObject->expects($this->once())->method('validate')->will($this->returnValue(new \TYPO3\Flow\Error\Result()));

        $errors = new \TYPO3\Flow\Error\Result();
        $errors->addError(new \TYPO3\Flow\Error\Error('Error', 123));
        $secondValidatorObject = $this->createMock(\TYPO3\Flow\Validation\Validator\ValidatorInterface::class);
        $secondValidatorObject->expects($this->once())->method('validate')->will($this->returnValue($errors));

        $thirdValidatorObject = $this->createMock(\TYPO3\Flow\Validation\Validator\ValidatorInterface::class);
        $thirdValidatorObject->expects($this->once())->method('validate')->will($this->returnValue(new \TYPO3\Flow\Error\Result()));

        $validatorConjunction->addValidator($validatorObject);
        $validatorConjunction->addValidator($secondValidatorObject);
        $validatorConjunction->addValidator($thirdValidatorObject);

        $validatorConjunction->validate('some subject');
    }

    /**
     * @test
     */
    public function validatorConjunctionReturnsNoErrorsIfAllJunctionedValidatorsReturnNoErrors()
    {
        $validatorConjunction = new \TYPO3\Flow\Validation\Validator\ConjunctionValidator(array());
        $validatorObject = $this->createMock(\TYPO3\Flow\Validation\Validator\ValidatorInterface::class);
        $validatorObject->expects($this->any())->method('validate')->will($this->returnValue(new \TYPO3\Flow\Error\Result()));

        $secondValidatorObject = $this->createMock(\TYPO3\Flow\Validation\Validator\ValidatorInterface::class);
        $secondValidatorObject->expects($this->any())->method('validate')->will($this->returnValue(new \TYPO3\Flow\Error\Result()));

        $validatorConjunction->addValidator($validatorObject);
        $validatorConjunction->addValidator($secondValidatorObject);

        $this->assertFalse($validatorConjunction->validate('some subject')->hasErrors());
    }

    /**
     * @test
     */
    public function validatorConjunctionReturnsErrorsIfOneValidatorReturnsErrors()
    {
        $validatorConjunction = new \TYPO3\Flow\Validation\Validator\ConjunctionValidator(array());
        $validatorObject = $this->createMock(\TYPO3\Flow\Validation\Validator\ValidatorInterface::class);

        $errors = new \TYPO3\Flow\Error\Result();
        $errors->addError(new \TYPO3\Flow\Error\Error('Error', 123));

        $validatorObject->expects($this->any())->method('validate')->will($this->returnValue($errors));

        $validatorConjunction->addValidator($validatorObject);

        $this->assertTrue($validatorConjunction->validate('some subject')->hasErrors());
    }

    /**
     * @test
     */
    public function removingAValidatorOfTheValidatorConjunctionWorks()
    {
        $validatorConjunction = $this->getAccessibleMock(\TYPO3\Flow\Validation\Validator\ConjunctionValidator::class, array('dummy'), array(array()), '', true);

        $validator1 = $this->createMock(\TYPO3\Flow\Validation\Validator\ValidatorInterface::class);
        $validator2 = $this->createMock(\TYPO3\Flow\Validation\Validator\ValidatorInterface::class);

        $validatorConjunction->addValidator($validator1);
        $validatorConjunction->addValidator($validator2);

        $validatorConjunction->removeValidator($validator1);

        $this->assertFalse($validatorConjunction->_get('validators')->contains($validator1));
        $this->assertTrue($validatorConjunction->_get('validators')->contains($validator2));
    }

    /**
     * @test
     * @expectedException \TYPO3\Flow\Validation\Exception\NoSuchValidatorException
     */
    public function removingANotExistingValidatorIndexThrowsException()
    {
        $validatorConjunction = new \TYPO3\Flow\Validation\Validator\ConjunctionValidator(array());
        $validator = $this->createMock(\TYPO3\Flow\Validation\Validator\ValidatorInterface::class);
        $validatorConjunction->removeValidator($validator);
    }

    /**
     * @test
     */
    public function countReturnesTheNumberOfValidatorsContainedInTheConjunction()
    {
        $validatorConjunction = new \TYPO3\Flow\Validation\Validator\ConjunctionValidator(array());

        $validator1 = $this->createMock(\TYPO3\Flow\Validation\Validator\ValidatorInterface::class);
        $validator2 = $this->createMock(\TYPO3\Flow\Validation\Validator\ValidatorInterface::class);

        $this->assertSame(0, count($validatorConjunction));

        $validatorConjunction->addValidator($validator1);
        $validatorConjunction->addValidator($validator2);

        $this->assertSame(2, count($validatorConjunction));
    }
}
